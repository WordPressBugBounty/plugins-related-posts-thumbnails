<?php
/**
 * Related post ID query builder.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds related post ID lists from taxonomy and post table queries.
 *
 * @since 6.0.0
 */
class RPT_Related_Query {

	/**
	 * Plugin instance for defaults.
	 *
	 * @var RelatedPostsThumbnails
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param RelatedPostsThumbnails $plugin Plugin instance.
	 * @since 6.0.0
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Query related post IDs for a source post.
	 *
	 * @param int                  $post_id    Source post ID.
	 * @param array<string, mixed> $query_args Query arguments.
	 * @param string               $debug      Debug output passed by reference.
	 * @return array<int>
	 * @since 6.0.0
	 */
	public function rpt_query_related_post_ids( $post_id, $query_args, &$debug = '' ) {
		$post_id = absint( $post_id );

		if ( $post_id <= 0 ) {
			return array();
		}

		$posts_number = absint( $query_args['posts_number'] ?? 0 );
		$sort_by      = rpt_sanitize_related_sort_by( $query_args['sort_by'] ?? RPT_RELATED_SORT_RAND );
		$author_id    = absint( $query_args['author_id'] ?? 0 );
		$exclude      = $query_args['exclude'] ?? '';

		if ( $posts_number <= 0 ) {
			return array();
		}

		$relation = $this->rpt_get_relation();
		$query_scope         = rpt_settings()->rpt_get( 'general.query_scope', array( 'mode' => 'all', 'category_ids' => array() ) );
		$display_scope       = rpt_settings()->rpt_get( 'general.display_scope', array( 'mode' => 'all', 'category_ids' => array() ) );
		$categories_show_all = rpt_settings_scope_is_all( $display_scope ) ? '1' : '0';
		$thsource            = (string) rpt_settings()->rpt_get( 'thumbnails.thsource', $this->plugin->thsource );
		$onlywiththumbs      = ( current_theme_supports( 'post-thumbnails' ) && 'post-thumbnails' === $thsource )
			? rpt_settings_bool( 'thumbnails.only_with_thumbs' )
			: false;

		$debug     .= "Relation: $relation; All categories: $categories_show_all;";
		$use_filter = ( '1' !== $categories_show_all || 'no' !== $relation );

		$related_objects = array();

		if ( $use_filter ) {
			$related_objects = $this->rpt_get_related_object_ids(
				$post_id,
				$relation,
				$categories_show_all,
				$display_scope,
				$query_scope,
				$debug
			);

			if ( false === $related_objects ) {
				return array();
			}
		}

		$selected_post_types = $this->rpt_get_selected_post_types( $post_id );

		if ( empty( $selected_post_types ) ) {
			$debug .= __( 'No posts matching relationships criteria;', 'related-posts-thumbnails' );

			return array();
		}

		$posts_in = $this->rpt_query_post_ids(
			$post_id,
			$selected_post_types,
			$sort_by,
			$posts_number,
			$author_id,
			$use_filter,
			$related_objects,
			$onlywiththumbs,
			$debug
		);

		if ( empty( $posts_in ) ) {
			return array();
		}

		return $this->rpt_apply_exclusions( $posts_in, $exclude );
	}

	/**
	 * Resolve the configured relation type.
	 *
	 * @return string
	 */
	private function rpt_get_relation() {
		$relation = sanitize_text_field( (string) rpt_settings()->rpt_get( 'relations.relation', $this->plugin->relation ) );

		if ( '' === $relation || '0' === $relation ) {
			return $this->plugin->relation;
		}

		return $relation;
	}

	/**
	 * Taxonomies used for a relation mode.
	 *
	 * @param string $relation Relation type.
	 * @return array<int, string>
	 */
	private function rpt_get_relation_taxonomies( $relation ) {
		if ( 'categories' === $relation ) {
			return array( 'category' );
		}

		if ( 'tags' === $relation ) {
			return array( 'post_tag' );
		}

		if ( 'custom' === $relation ) {
			return (array) rpt_settings()->rpt_get( 'relations.custom_taxonomies', $this->plugin->custom_taxonomies );
		}

		return array( 'category', 'post_tag' );
	}

	/**
	 * Map term IDs to term_taxonomy IDs.
	 *
	 * @param array<int> $term_ids Term IDs.
	 * @return array<int, int|string>
	 */
	private function rpt_get_term_taxonomy_ids( $term_ids ) {
		global $wpdb;

		list( $placeholders, $values ) = $this->rpt_int_in_placeholders( $term_ids );

		if ( '' === $placeholders ) {
			return array();
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Placeholders/%d list built from absint IDs only.
		$results = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- See ignore above.
			$wpdb->prepare(
				"SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE term_id IN ($placeholders)",
				...$values
			)
		);
		$taxonomy = array();

		if ( is_array( $results ) && count( $results ) > 0 ) {
			foreach ( $results as $item ) {
				$taxonomy[] = absint( $item->term_taxonomy_id );
			}
		}

		return $taxonomy;
	}

	/**
	 * Object IDs matching relation and display-scope filters.
	 *
	 * @param int                  $post_id             Source post ID.
	 * @param string               $relation            Relation type.
	 * @param string               $categories_show_all Whether all display categories are allowed.
	 * @param array<string, mixed> $display_scope       Display scope setting.
	 * @param array<string, mixed> $query_scope         Query scope setting.
	 * @param string               $debug               Debug output passed by reference.
	 * @return array<int>|false Object IDs, or false when the query should abort.
	 * @since 6.0.0
	 */
	private function rpt_get_related_object_ids( $post_id, $relation, $categories_show_all, $display_scope, $query_scope, &$debug ) { // NOSONAR — if statements
		global $wpdb;

		$query_objects = "SELECT DISTINCT object_id FROM {$wpdb->term_relationships} WHERE 1=1";
		$prepare_args  = array();

		if ( 'no' !== $relation ) {
			$taxonomy     = $this->rpt_get_relation_taxonomies( $relation );
			$object_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

			if ( empty( $object_terms ) || ! is_array( $object_terms ) ) {
				$debug .= __( 'No taxonomy terms to get posts;', 'related-posts-thumbnails' );

				return false;
			}

			$object_taxonomy_a = $this->rpt_get_term_taxonomy_ids( $object_terms );
			list( $placeholders, $values ) = $this->rpt_int_in_placeholders( $object_taxonomy_a );

			if ( '' === $placeholders ) {
				$debug .= __( 'No taxonomy terms to get posts;', 'related-posts-thumbnails' );

				return false;
			}

			$query_objects .= " AND term_taxonomy_id IN ($placeholders)";
			$prepare_args    = array_merge( $prepare_args, $values );
		}

		if ( '1' !== $categories_show_all ) {
			$select_terms = rpt_settings_scope_ids( $display_scope );

			if ( empty( $select_terms ) ) {
				$select_terms = rpt_settings_scope_ids( $query_scope );
			}

			if ( empty( $select_terms ) || ! is_array( $select_terms ) ) {
				$debug .= __( 'No categories were selected;', 'related-posts-thumbnails' );

				return false;
			}

			$filter_taxonomy_a = $this->rpt_get_term_taxonomy_ids( $select_terms );
			list( $placeholders, $values ) = $this->rpt_int_in_placeholders( $filter_taxonomy_a );

			if ( '' === $placeholders ) {
				$debug .= __( 'No categories were selected;', 'related-posts-thumbnails' );

				return false;
			}

			if ( 'no' !== $relation ) {
				$query_objects .= " AND object_id IN (SELECT DISTINCT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ($placeholders))";
			} else {
				$query_objects .= " AND term_taxonomy_id IN ($placeholders)";
			}

			$prepare_args = array_merge( $prepare_args, $values );
		}

		if ( empty( $prepare_args ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Static SQL with no placeholders when no taxonomy filters apply.
			$relationships = $wpdb->get_results( $query_objects );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Placeholders built from absint IDs via rpt_int_in_placeholders().
			$relationships = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- See ignore above.
				$wpdb->prepare( $query_objects, ...$prepare_args )
			);
		}
		$related_objects = array();

		if ( is_array( $relationships ) && count( $relationships ) > 0 ) {
			foreach ( $relationships as $item ) {
				$related_objects[] = absint( $item->object_id );
			}
		}

		return $related_objects;
	}

	/**
	 * Resolve allowed post types for the related query.
	 *
	 * @param int $post_id Source post ID.
	 * @return array<int, string>
	 * @since 6.0.0
	 */
	private function rpt_get_selected_post_types( $post_id ) {
		$custom_relations = apply_filters( 'rpt_custom_relationship', array( get_post_type( $post_id ) ) );

		if ( ! is_array( $custom_relations ) ) {
			$custom_relations = array( $custom_relations );
		}

		$registered_post_types = get_post_types();
		$selected_post_types   = array();

		foreach ( $custom_relations as $checked_post_type ) {
			$checked_post_type = sanitize_key( (string) $checked_post_type );

			if ( in_array( $checked_post_type, $registered_post_types, true ) ) {
				$selected_post_types[] = $checked_post_type;
			}
		}

		return array_values( array_unique( $selected_post_types ) );
	}

	/**
	 * Run the posts table query and return matching IDs.
	 *
	 * @param int                $post_id             Source post ID.
	 * @param array<int, string> $selected_post_types Allowed post types.
	 * @param string             $sort_by             Safe ORDER BY fragment.
	 * @param int         $posts_number      Number of posts.
	 * @param int         $author_id         Optional author filter.
	 * @param bool        $use_filter        Whether taxonomy filters apply.
	 * @param array<int>  $related_objects   Related object IDs.
	 * @param bool        $onlywiththumbs    Whether a thumbnail is required.
	 * @param string      $debug             Debug output passed by reference.
	 * @return array<int>
	 * @since 6.0.0
	 */
	private function rpt_query_post_ids( $post_id, $selected_post_types, $sort_by, $posts_number, $author_id, $use_filter, $related_objects, $onlywiththumbs, &$debug ) { // NOSONAR — params
		global $wpdb;

		list( $post_type_placeholders, $post_type_values ) = $this->rpt_string_in_placeholders( $selected_post_types );

		if ( '' === $post_type_placeholders ) {
			return array();
		}

		$where_parts  = array(
			"post_type IN ($post_type_placeholders)",
			"post_status = 'publish'",
			'ID <> %d',
		);
		$prepare_args = array_merge( $post_type_values, array( $post_id ) );
		$startdate    = (string) rpt_settings()->rpt_get( 'general.start_date', '' );

		if ( ! empty( $startdate ) && preg_match( '/^\d\d\d\d-\d\d-\d\d$/', $startdate ) ) {
			$debug         .= "Startdate: $startdate;";
			$where_parts[]  = 'post_date >= %s';
			$prepare_args[] = $startdate;
		}

		if ( ! empty( $author_id ) ) {
			$where_parts[]  = 'post_author = %d';
			$prepare_args[] = $author_id;
			$debug         .= 'Author filter: ' . $author_id . ';';
		}

		if ( $use_filter ) {
			if ( empty( $related_objects ) ) {
				$debug .= __( 'No posts matching relationships criteria;', 'related-posts-thumbnails' );

				return array();
			}

			list( $related_placeholders, $related_values ) = $this->rpt_int_in_placeholders( $related_objects );

			if ( '' === $related_placeholders ) {
				$debug .= __( 'No posts matching relationships criteria;', 'related-posts-thumbnails' );

				return array();
			}

			$where_parts[] = "ID IN ($related_placeholders)";
			$prepare_args  = array_merge( $prepare_args, $related_values );
		}

		$join = '';

		if ( $onlywiththumbs ) {
			$debug .= 'Only with thumbnails;';
			$join   = " INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id)";
			$where_parts[] = "{$wpdb->postmeta}.meta_key = '_thumbnail_id'";
		}

		$prepare_args[] = $posts_number;
		$sql            = "SELECT ID FROM {$wpdb->posts}{$join} WHERE " . implode( ' AND ', $where_parts )
			. ' ORDER BY ' . $sort_by
			. ' LIMIT %d';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Dynamic IN placeholders from sanitized IDs/post types; ORDER BY from rpt_sanitize_related_sort_by() whitelist.
		$random_posts = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- See ignore above.
			$wpdb->prepare( $sql, ...$prepare_args )
		);

		if ( ! is_array( $random_posts ) || count( $random_posts ) < 1 ) {
			$debug .= __( 'No posts matching relationships criteria;', 'related-posts-thumbnails' );

			return array();
		}

		$posts_in = array();

		foreach ( $random_posts as $random_post ) {
			$posts_in[] = $random_post->ID;
		}

		return $posts_in;
	}

	/**
	 * Remove explicitly excluded post IDs.
	 *
	 * @param array<int>     $posts_in Related IDs.
	 * @param array|string   $exclude  Excluded IDs.
	 * @return array<int>
	 * @since 6.0.0
	 */
	private function rpt_apply_exclusions( $posts_in, $exclude ) {
		if ( empty( $exclude ) ) {
			return $posts_in;
		}

		if ( is_array( $exclude ) ) {
			$exclude_post_ids = array_map( 'absint', $exclude );
		} else {
			$exclude_post_ids = array_map( 'absint', explode( ',', (string) $exclude ) );
		}

		return array_values( array_diff( $posts_in, $exclude_post_ids ) );
	}

	/**
	 * Build integer IN() placeholders and values for $wpdb->prepare().
	 *
	 * @param array<int|string> $ids Raw IDs.
	 * @return array{0: string, 1: array<int>} Placeholder list and integer values.
	 * @since 6.0.0
	 */
	private function rpt_int_in_placeholders( $ids ) {
		$ids = array_values(
			array_filter(
				array_map( 'absint', (array) $ids )
			)
		);

		if ( empty( $ids ) ) {
			return array( '', array() );
		}

		return array(
			implode( ', ', array_fill( 0, count( $ids ), '%d' ) ),
			$ids,
		);
	}

	/**
	 * Build string IN() placeholders and values for $wpdb->prepare().
	 *
	 * @param array<int, string> $values Raw string values.
	 * @return array{0: string, 1: array<int, string>} Placeholder list and string values.
	 * @since 6.0.0
	 */
	private function rpt_string_in_placeholders( $values ) {
		$values = array_values(
			array_filter(
				array_map(
					static function ( $value ) {
						return sanitize_text_field( (string) $value );
					},
					(array) $values
				)
			)
		);

		if ( empty( $values ) ) {
			return array( '', array() );
		}

		return array(
			implode( ', ', array_fill( 0, count( $values ), '%s' ) ),
			$values,
		);
	}
}
