<?php
/**
 * Settings service for Related Posts Thumbnails REST API.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles reading and saving plugin settings via rpt_settings().
 *
 * @since 6.0.0
 */
class RPT_Settings_Service {

	/**
	 * Plugin instance used for defaults and capability checks.
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
	 * Get all settings grouped by tab.
	 *
	 * @return array<string, array<string, mixed>>
	 * @since 6.0.0
	 */
	public function rpt_get_settings() {
		return array(
			'general'    => $this->rpt_get_general_settings(),
			'thumbnails' => $this->rpt_get_thumbnail_settings(),
			'styles'     => $this->rpt_get_style_settings(),
			'relations'  => $this->rpt_get_relation_settings(),
		);
	}

	/**
	 * Get meta data for the settings UI.
	 *
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	public function rpt_get_meta() {
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		$categories = get_categories( array( 'hide_empty' => false ) );

		$category_options = array();
		foreach ( $categories as $category ) {
			$category_options[] = array(
				'value' => (string) $category->term_id,
				'label' => $category->name,
			);
		}

		$post_type_options = array();
		foreach ( $post_types as $post_type ) {
			// Media (attachment) is not useful for related posts selection.
			if ( 'attachment' === $post_type->name ) {
				continue;
			}

			$post_type_options[] = array(
				'value' => $post_type->name,
				'label' => $post_type->labels->singular_name,
			);
		}

		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		$taxonomy_options = array();
		foreach ( $taxonomies as $taxonomy ) {
			$taxonomy_options[] = array(
				'value' => $taxonomy->name,
				'label' => $taxonomy->labels->singular_name,
			);
		}

		$thumbnail_sizes = array();

		foreach ( get_intermediate_image_sizes() as $size_name ) {
			$thumbnail_sizes[ $size_name ] = $size_name;
		}

		/**
		 * Filter thumbnail size options shown in the settings UI.
		 *
		 * @since 5.0.0
		 *
		 * @param array<string, string> $thumbnail_sizes Size slug => label pairs.
		 */
		$thumbnail_sizes = apply_filters( 'rpt_thumbnailsizes', $thumbnail_sizes );

		$size_options = array();
		foreach ( $thumbnail_sizes as $value => $label ) {
			$size_options[] = array(
				'value' => (string) $value,
				'label' => (string) $label,
			);
		}

		return array(
			'postTypes'          => $post_type_options,
			'categories'         => $category_options,
			'taxonomies'         => $taxonomy_options,
			'thumbnailSizes'     => $size_options,
			'hasMultipleAuthors' => $this->plugin->rpt_has_multiple_publishers(),
			'hasPostThumbnails'  => current_theme_supports( 'post-thumbnails' ),
			'defaultImageUrl'    => $this->plugin->rpt_get_default_image_url(),
			'pluginDefaultImage' => esc_url( plugins_url( 'img/default.png', dirname( __DIR__ ) . '/related-posts-thumbnails.php' ) ),
			'supportUrl'         => 'https://wpbrigade.com/contact',
			'phpSnippet'         => '<?php if ( function_exists( "get_related_posts_thumbnails" ) ) { get_related_posts_thumbnails(); } ?>',
			'shortcode'          => '[related-posts-thumbnails]',
			'siteDateFormat'     => (string) get_option( 'date_format', 'F j, Y' ),
			'migration'          => $this->rpt_get_migration_meta(),
		);
	}

	/**
	 * Migration consent state for the settings admin UI.
	 *
	 * @return array{required: bool}
	 * @since 6.0.0
	 */
	public function rpt_get_migration_meta() {
		return array(
			'required' => rpt_settings()->rpt_needs_migration_consent(),
		);
	}

	/**
	 * Run legacy storage migration after admin consent.
	 *
	 * @return array<string, mixed>|WP_Error
	 * @since 6.0.0
	 */
	public function rpt_run_settings_migration() {
		if ( ! rpt_settings()->rpt_needs_migration_consent() ) {
			return array(
				'success'  => true,
				'settings' => $this->rpt_get_settings(),
				'meta'     => $this->rpt_get_meta(),
			);
		}

		if ( ! rpt_settings()->rpt_run_migration() ) {
			return new WP_Error(
				'rpt_migration_failed',
				__( 'Settings storage upgrade failed. Please try again or contact support.', 'related-posts-thumbnails' ),
				array( 'status' => 500 )
			);
		}

		return array(
			'success'  => true,
			'settings' => $this->rpt_get_settings(),
			'meta'     => $this->rpt_get_meta(),
			'notices'  => array(
				array(
					'type'    => 'success',
					'message' => __( 'Settings storage upgraded successfully.', 'related-posts-thumbnails' ),
				),
			),
		);
	}

	/**
	 * Update settings from REST payload.
	 *
	 * @param array<string, mixed> $payload Settings payload.
	 * @return array<string, mixed>|WP_Error
	 * @since 6.0.0
	 */
	public function rpt_update_settings( $payload ) {
		if ( ! is_array( $payload ) ) {
			return new WP_Error(
				'rpt_invalid_payload',
				__( 'Invalid settings payload.', 'related-posts-thumbnails' ),
				array( 'status' => 400 )
			);
		}

		if ( rpt_settings()->rpt_needs_migration_consent() ) {
			return new WP_Error(
				'rpt_migration_required',
				__( 'Please upgrade your settings storage before saving changes.', 'related-posts-thumbnails' ),
				array( 'status' => 403 )
			);
		}

		$general    = isset( $payload['general'] ) && is_array( $payload['general'] ) ? $payload['general'] : array();
		$thumbnails = isset( $payload['thumbnails'] ) && is_array( $payload['thumbnails'] ) ? $payload['thumbnails'] : array();
		$styles     = isset( $payload['styles'] ) && is_array( $payload['styles'] ) ? $payload['styles'] : array();
		$relations  = isset( $payload['relations'] ) && is_array( $payload['relations'] ) ? $payload['relations'] : array();

		$default_image_type = isset( $general['defaultImageType'] )
			? sanitize_text_field( $general['defaultImageType'] )
			: 'image';

		if ( ! in_array( $default_image_type, array( 'image', 'url' ), true ) ) {
			$default_image_type = 'image';
		}

		if ( 'url' === $default_image_type && isset( $general['defaultImageUrl'] ) ) {
			$raw_image_url = trim( (string) $general['defaultImageUrl'] );
			if ( '' !== $raw_image_url && ! rpt_is_valid_remote_image_url( $raw_image_url ) ) {
				return new WP_Error(
					'rpt_invalid_image_url',
					__( 'Please enter a valid image URL (for example .jpg, .png, .gif, or .webp).', 'related-posts-thumbnails' ),
					array( 'status' => 400 )
				);
			}
		}

		// Resolve against the currently stored value so a partial payload that omits
		// 'relation' or 'customTaxonomies' is still validated against the effective,
		// post-merge state rather than only the fields present in this request.
		$relation = isset( $relations['relation'] )
			? sanitize_text_field( (string) $relations['relation'] )
			: (string) rpt_settings()->rpt_get( 'relations.relation', '' );

		if ( 'custom' === $relation ) {
			$custom_taxonomies = isset( $relations['customTaxonomies'] ) && is_array( $relations['customTaxonomies'] )
				? array_filter( array_map( 'sanitize_text_field', $relations['customTaxonomies'] ) )
				: array_filter( (array) rpt_settings()->rpt_get( 'relations.custom_taxonomies', array() ) );

			if ( empty( $custom_taxonomies ) ) {
				return new WP_Error(
					'rpt_custom_taxonomies_required',
					__( 'Custom relation requires at least one taxonomy. Select a taxonomy or choose a different relation.', 'related-posts-thumbnails' ),
					array( 'status' => 400 )
				);
			}
		}

		$old_query_settings = rpt_get_query_affecting_settings();
		$old_cache_enabled  = rpt_settings_bool( 'general.cache_enabled', true );

		$settings = rpt_settings()->rpt_all();
		$settings['general']    = array_merge( $settings['general'], $this->rpt_map_general_to_storage( $general ) );
		$settings['thumbnails'] = array_merge( $settings['thumbnails'], $this->rpt_map_thumbnails_to_storage( $thumbnails ) );
		$settings['styles']     = array_merge( $settings['styles'], $this->rpt_map_styles_to_storage( $styles ) );
		$settings['relations']  = array_merge( $settings['relations'], $this->rpt_map_relations_to_storage( $relations ) );

		rpt_settings()->rpt_save( $settings );

		$cache_purged = false;
		// Toggling the cache also purges: while disabled, post saves don't clear entries,
		// so re-enabling could otherwise serve stale IDs still within their TTL.
		$cache_toggled = rpt_settings_bool( 'general.cache_enabled', true ) !== $old_cache_enabled;

		if ( $cache_toggled || rpt_query_settings_changed( $old_query_settings, rpt_get_query_affecting_settings() ) ) {
			rpt_purge_all_related_cache();
			$cache_purged = true;
		}

		return array(
			'success'     => true,
			'settings'    => $this->rpt_get_settings(),
			'cachePurged' => $cache_purged,
			'notices'     => array(
				array(
					'type'    => 'success',
					'message' => __( 'Settings updated', 'related-posts-thumbnails' ),
				),
			),
		);
	}

	/**
	 * Get general tab settings for REST.
	 *
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_get_general_settings() {
		$general     = rpt_settings()->rpt_get( 'general', array() );
		$query_scope = $general['query_scope'] ?? array( 'mode' => 'all', 'category_ids' => array() );
		$display     = $general['display_scope'] ?? array( 'mode' => 'all', 'category_ids' => array() );

		return array(
			'auto'                => rpt_settings_yes_no( 'general.auto', '1' ),
			'devMode'             => rpt_settings_yes_no( 'general.dev_mode', '0' ),
			'cacheEnabled'        => rpt_settings_yes_no( 'general.cache_enabled', '1' ),
			'authorRelated'       => rpt_settings_yes_no( 'general.author_related', '0' ),
			'singleOnly'          => rpt_settings_yes_no( 'general.single_only', '1' ),
			'postTypes'           => $this->rpt_normalize_post_types( $general['post_types'] ?? array( 'post' ) ),
			'categoriesAll'       => rpt_settings_scope_is_all( $query_scope ) ? '1' : '0',
			'categories'          => rpt_settings_scope_ids( $query_scope ),
			'categoriesAllUi'     => rpt_settings_scope_is_all( $query_scope ) ? array( '0' ) : rpt_settings_scope_ids( $query_scope ),
			'showCategoriesAll'   => rpt_settings_scope_is_all( $display ) ? '1' : '0',
			'showCategories'      => rpt_settings_scope_ids( $display ),
			'showCategoriesAllUi' => rpt_settings_scope_is_all( $display ) ? array( '0' ) : rpt_settings_scope_ids( $display ),
			'showTaxonomy'        => rpt_settings_yes_no( 'general.show_taxonomy', '0' ),
			'mobileView'          => rpt_settings_yes_no( 'general.mobile_view', '0' ),
			'startDate'           => (string) ( $general['start_date'] ?? '' ),
			'postSort'            => (string) ( $general['post_sort'] ?? 'rand' ),
			'topText'             => $this->rpt_get_top_text_for_ui(),
			'titleTag'            => (string) ( $general['title_tag'] ?? 'h2' ),
			'number'              => (int) ( $general['number'] ?? 3 ),
			'defaultImageType'    => (string) ( $general['default_image_type'] ?? 'image' ),
			'defaultImageUrl'     => (string) ( $general['default_image_url'] ?? '' ),
			'defaultImage'        => (string) ( $general['default_image'] ?? '' ),
			'showDate'            => rpt_settings_yes_no( 'general.show_date', '0' ),
			'dateFormat'          => (string) ( $general['date_format'] ?? 'j F, Y' ),
		);
	}

	/**
	 * Get thumbnails tab settings for REST.
	 *
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_get_thumbnail_settings() {
		$thumbnails = rpt_settings()->rpt_get( 'thumbnails', array() );

		return array(
			'thsource'          => (string) ( $thumbnails['thsource'] ?? 'post-thumbnails' ),
			'poststhname'       => (string) ( $thumbnails['poststhname'] ?? 'thumbnail' ),
			'onlywiththumbs'    => rpt_settings_yes_no( 'thumbnails.only_with_thumbs', '0' ),
			'articlefirstimage' => rpt_settings_article_first_image_enabled( true ) ? '1' : '0',
			'imageType'         => (string) ( $thumbnails['image_type'] ?? 'image' ),
			'customfield'       => (string) ( $thumbnails['customfield'] ?? '' ),
			'themeResizeUrl'    => (string) ( $thumbnails['theme_resize_url'] ?? '' ),
			'customwidth'       => (string) ( $thumbnails['custom_width'] ?? '100' ),
			'customheight'      => (string) ( $thumbnails['custom_height'] ?? '100' ),
		);
	}

	/**
	 * Get styles tab settings for REST.
	 *
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_get_style_settings() {
		$styles = rpt_settings()->rpt_get( 'styles', array() );

		return array(
			'outputStyle'     => $this->rpt_normalize_output_style( $styles['output_style'] ?? 'block' ),
			'cleanhtml'       => rpt_settings_yes_no( 'styles.clean_html', '0' ),
			'column'          => $this->rpt_normalize_column_value( $styles['column'] ?? '3', '3' ),
			'columnT'         => $this->rpt_normalize_column_value( $styles['column_tablet'] ?? '2', '2' ),
			'columnM'         => $this->rpt_normalize_column_value( $styles['column_mobile'] ?? '2', '2' ),
			'imageSize'       => $this->rpt_normalize_column_value( $styles['image_size'] ?? '1/1', '1/1' ),
			'background'      => (string) ( $styles['background'] ?? '#ffffff' ),
			'hoverbackground' => (string) ( $styles['hover_background'] ?? '#eeeeee' ),
			'bordercolor'     => (string) ( $styles['border_color'] ?? '#dddddd' ),
			'fontcolor'       => (string) ( $styles['font_color'] ?? '#333333' ),
			'fontfamily'      => (string) ( $styles['font_family'] ?? 'Arial' ),
			'fontsize'        => (string) ( $styles['font_size'] ?? '12' ),
			'spacing'         => (string) ( $styles['spacing'] ?? '10px' ),
			'textlength'      => (string) ( $styles['text_length'] ?? '100' ),
			'excerptlength'   => (string) ( $styles['excerpt_length'] ?? '0' ),
			'textblockheight' => (string) ( $styles['text_block_height'] ?? '75' ),
		);
	}

	/**
	 * Get relations tab settings for REST.
	 *
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_get_relation_settings() {
		$relations = rpt_settings()->rpt_get( 'relations', array() );

		return array(
			'relation'         => (string) ( $relations['relation'] ?? 'categories' ),
			'customTaxonomies' => (array) ( $relations['custom_taxonomies'] ?? array() ),
		);
	}

	/**
	 * Map REST general payload to storage keys.
	 *
	 * @param array<string, mixed> $data REST payload.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_map_general_to_storage( array $data ) {
		$mapped = array();

		$checkbox_map = array(
			'singleOnly'   => 'single_only',
			'mobileView'   => 'mobile_view',
			'showTaxonomy' => 'show_taxonomy',
			'showDate'     => 'show_date',
			'devMode'      => 'dev_mode',
			'cacheEnabled' => 'cache_enabled',
		);

		foreach ( $checkbox_map as $rest_key => $storage_key ) {
			if ( array_key_exists( $rest_key, $data ) ) {
				$mapped[ $storage_key ] = isset( $data[ $rest_key ] ) && '1' === (string) $data[ $rest_key ];
			}
		}

		if ( isset( $data['postTypes'] ) && is_array( $data['postTypes'] ) ) {
			$mapped['post_types'] = $this->rpt_normalize_post_types( $data['postTypes'] );
		}

		$author_related_enabled = $this->plugin->rpt_has_multiple_publishers()
			&& isset( $data['authorRelated'] )
			&& '1' === (string) $data['authorRelated'];

		if ( $author_related_enabled ) {
			$mapped['author_related'] = true;
			$mapped['auto']           = false;
		} else {
			$mapped['author_related'] = isset( $data['authorRelated'] ) && '1' === (string) $data['authorRelated'];
			if ( array_key_exists( 'auto', $data ) ) {
				$mapped['auto'] = '1' === (string) $data['auto'];
			}
		}

		if ( isset( $data['categoriesAll'] ) ) {
			$mapped['query_scope'] = rpt_settings_rest_scope_to_storage( $data['categoriesAll'] );
		}

		if ( isset( $data['showCategoriesAll'] ) ) {
			$mapped['display_scope'] = rpt_settings_rest_scope_to_storage( $data['showCategoriesAll'] );
		}

		if ( isset( $data['startDate'] ) ) {
			$mapped['start_date'] = sanitize_text_field( (string) $data['startDate'] );
		}

		if ( isset( $data['postSort'] ) ) {
			$sort = sanitize_text_field( (string) $data['postSort'] );
			$mapped['post_sort'] = in_array( $sort, array( 'rand', 'latest' ), true ) ? $sort : 'rand';
		}

		if ( isset( $data['topText'] ) ) {
			// Plain text only — wrapper tag comes from titleTag dropdown.
			$mapped['top_text'] = trim( wp_strip_all_tags( (string) $data['topText'] ) );
		}

		if ( isset( $data['titleTag'] ) ) {
			$mapped['title_tag'] = $this->rpt_sanitize_title_tag( $data['titleTag'] );
		}

		if ( isset( $data['number'] ) ) {
			$number = absint( $data['number'] );
			$number = $number > 0 ? $number : 3;
			$mapped['number'] = min( RPT_Settings_Schema::MAX_POSTS_NUMBER, $number );
		}

		if ( isset( $data['dateFormat'] ) ) {
			$mapped['date_format'] = sanitize_text_field( (string) $data['dateFormat'] );
		}

		if ( isset( $data['defaultImageType'] ) ) {
			$type = sanitize_text_field( (string) $data['defaultImageType'] );
			$mapped['default_image_type'] = in_array( $type, array( 'image', 'url' ), true ) ? $type : 'image';
		}

		if ( isset( $data['defaultImage'] ) ) {
			$mapped['default_image'] = sanitize_text_field( (string) $data['defaultImage'] );
		}

		if ( isset( $data['defaultImageUrl'] ) ) {
			$mapped['default_image_url'] = esc_url_raw( (string) $data['defaultImageUrl'] );
		}

		return $mapped;
	}

	/**
	 * Map REST thumbnails payload to storage keys.
	 *
	 * @param array<string, mixed> $data REST payload.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_map_thumbnails_to_storage( array $data ) {
		$mapped = array();
		$text_map = array(
			'thsource'       => 'thsource',
			'poststhname'    => 'poststhname',
			'customfield'    => 'customfield',
			'themeResizeUrl' => 'theme_resize_url',
			'customwidth'    => 'custom_width',
			'customheight'   => 'custom_height',
		);

		foreach ( $text_map as $rest_key => $storage_key ) {
			if ( isset( $data[ $rest_key ] ) ) {
				$mapped[ $storage_key ] = sanitize_text_field( (string) $data[ $rest_key ] );
			}
		}

		if ( array_key_exists( 'onlywiththumbs', $data ) ) {
			$mapped['only_with_thumbs'] = '1' === (string) $data['onlywiththumbs'];
		}

		$only_with_thumbs = array_key_exists( 'only_with_thumbs', $mapped )
			? $mapped['only_with_thumbs']
			: rpt_settings_bool( 'thumbnails.only_with_thumbs' );

		if ( array_key_exists( 'articlefirstimage', $data ) ) {
			$mapped['article_first_image'] = ! $only_with_thumbs && '1' === (string) $data['articlefirstimage'];
		} elseif ( $only_with_thumbs ) {
			$mapped['article_first_image'] = false;
		}

		if ( isset( $data['imageType'] ) ) {
			$image_type = sanitize_text_field( (string) $data['imageType'] );
			$mapped['image_type'] = in_array( $image_type, array( 'image', 'url' ), true ) ? $image_type : 'image';
		}

		return $mapped;
	}

	/**
	 * Map REST styles payload to storage keys.
	 *
	 * @param array<string, mixed> $data REST payload.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_map_styles_to_storage( array $data ) {
		$mapped = array();

		if ( isset( $data['outputStyle'] ) ) {
			$mapped['output_style'] = $this->rpt_normalize_output_style( sanitize_text_field( (string) $data['outputStyle'] ) );
		}

		if ( array_key_exists( 'cleanhtml', $data ) ) {
			$mapped['clean_html'] = '1' === (string) $data['cleanhtml'];
		}

		if ( isset( $data['column'] ) ) {
			$mapped['column'] = rpt_sanitize_column_count( $data['column'], $this->plugin->relpoststh_column );
		}

		if ( isset( $data['columnT'] ) ) {
			$mapped['column_tablet'] = rpt_sanitize_column_count( $data['columnT'], $this->plugin->relpoststh_column_t );
		}

		if ( isset( $data['columnM'] ) ) {
			$mapped['column_mobile'] = rpt_sanitize_column_count( $data['columnM'], $this->plugin->relpoststh_column_m );
		}

		if ( isset( $data['imageSize'] ) ) {
			$mapped['image_size'] = rpt_sanitize_aspect_ratio( $data['imageSize'], $this->plugin->relpoststh_image_size );
		}

		$color_map = array(
			'background'      => 'background',
			'hoverbackground' => 'hover_background',
			'bordercolor'     => 'border_color',
			'fontcolor'       => 'font_color',
		);

		foreach ( $color_map as $rest_key => $storage_key ) {
			if ( isset( $data[ $rest_key ] ) ) {
				$default = '#ffffff';
				if ( 'hover_background' === $storage_key ) {
					$default = '#eeeeee';
				} elseif ( 'border_color' === $storage_key ) {
					$default = '#dddddd';
				} elseif ( 'font_color' === $storage_key ) {
					$default = '#333333';
				}
				$mapped[ $storage_key ] = rpt_sanitize_hex_color( $data[ $rest_key ], $default );
			}
		}

		if ( isset( $data['fontfamily'] ) ) {
			$mapped['font_family'] = rpt_sanitize_css_font_family( $data['fontfamily'], 'Arial' );
		}

		if ( isset( $data['fontsize'] ) ) {
			$mapped['font_size'] = rpt_sanitize_css_font_size( $data['fontsize'], '12' );
		}

		if ( isset( $data['spacing'] ) ) {
			$mapped['spacing'] = rpt_sanitize_css_length( $data['spacing'], '10px' );
		}

		if ( isset( $data['textlength'] ) ) {
			$mapped['text_length'] = rpt_sanitize_positive_int_string( $data['textlength'], '100', 0, 500 );
		}

		if ( isset( $data['excerptlength'] ) ) {
			$mapped['excerpt_length'] = rpt_sanitize_positive_int_string( $data['excerptlength'], '0', 0, 500 );
		}

		if ( isset( $data['textblockheight'] ) ) {
			$mapped['text_block_height'] = rpt_sanitize_positive_int_string( $data['textblockheight'], '75', 0, 500 );
		}

		return $mapped;
	}

	/**
	 * Map REST relations payload to storage keys.
	 *
	 * @param array<string, mixed> $data REST payload.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_map_relations_to_storage( array $data ) {
		$mapped = array();

		if ( isset( $data['relation'] ) ) {
			$mapped['relation'] = sanitize_text_field( (string) $data['relation'] );
		}

		if ( isset( $data['customTaxonomies'] ) && is_array( $data['customTaxonomies'] ) ) {
			$mapped['custom_taxonomies'] = array_map( 'sanitize_text_field', $data['customTaxonomies'] );
		} elseif ( array_key_exists( 'customTaxonomies', $data ) ) {
			$mapped['custom_taxonomies'] = array();
		}

		return $mapped;
	}

	/**
	 * Normalize post type values for the UI and storage.
	 *
	 * @param mixed $types Raw post types value.
	 * @return array<int, string>
	 * @since 6.0.0
	 */
	private function rpt_normalize_post_types( $types ) {
		$types = array_values(
			array_filter(
				array_map( 'sanitize_text_field', array_map( 'strval', (array) $types ) ),
				static function ( $type ) {
					return '' !== $type && 'attachment' !== $type;
				}
			)
		);

		if ( empty( $types ) ) {
			return array( 'post' );
		}

		return $types;
	}

	/**
	 * Normalize legacy output style values for the React UI.
	 *
	 * @param mixed $style Raw output style.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_normalize_output_style( $style ) {
		$style = sanitize_text_field( (string) $style );

		if ( '' === $style || 'div' === $style ) {
			return 'block';
		}

		return $style;
	}

	/**
	 * Apply plugin column defaults when an option is empty.
	 *
	 * @param mixed  $value   Stored option value.
	 * @param string $default Plugin default.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_normalize_column_value( $value, $default ) {
		$value = sanitize_text_field( (string) $value );

		if ( '' === $value ) {
			return (string) $default;
		}

		return $value;
	}

	/**
	 * Sanitize title tag for storage.
	 *
	 * @param string $title_tag Title tag value.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_sanitize_title_tag( $title_tag ) {
		$title_tag    = sanitize_text_field( (string) $title_tag );
		$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'strong' );

		if ( ! in_array( $title_tag, $allowed_tags, true ) ) {
			$title_tag = 'h2';
		}

		return $title_tag;
	}

	/**
	 * Get top text for the settings UI.
	 *
	 * Returns the translated default when stored top text is empty.
	 *
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_get_top_text_for_ui() {
		$stored = (string) rpt_settings()->rpt_get( 'general.top_text', '' );

		if ( '' === trim( $stored ) ) {
			return __( 'Related posts:', 'related-posts-thumbnails' );
		}

		return wp_strip_all_tags( $stored );
	}
}
