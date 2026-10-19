<?php
/**
 * Migration from legacy flat options to tab-based storage.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds tab options from legacy data and manages storage_version meta.
 *
 * @since 6.0.0
 */
class RPT_Settings_Migration {

	/**
	 * Plugin instance for class-property fallbacks during legacy import.
	 *
	 * @var RelatedPostsThumbnails|null
	 */
	private $plugin;

	/**
	 * @param RelatedPostsThumbnails|null $plugin Plugin instance for class-property fallbacks.
	 * @since 6.0.0
	 */
	public function __construct( $plugin = null ) {
		$this->plugin = $plugin;
	}

	/**
	 * Initialize or finalize storage on plugin load.
	 *
	 * Migrated sites prune legacy rows once (see legacy_pruned in schema meta).
	 * Fresh installs are initialized immediately.
	 * Legacy upgrade sites defer migration until admin consent (see rpt_run_migration).
	 *
	 * @return bool True when fresh storage was initialized on this request.
	 * @since 6.0.0
	 */
	public function rpt_ensure_storage() {
		if ( $this->rpt_is_migrated() ) {
			if ( ! $this->rpt_is_legacy_pruned() ) {
				$this->rpt_prune_legacy_storage();
			}

			return false;
		}

		if ( $this->rpt_has_legacy_data() ) {
			return false;
		}

		return $this->rpt_initialize_fresh_storage();
	}

	/**
	 * Whether tab-based storage is active for this site.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public function rpt_is_migrated() {
		return $this->rpt_get_storage_version() >= RPT_Settings_Schema::STORAGE_VERSION;
	}

	/**
	 * Whether legacy flat options exist and admin consent is required before migrating.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public function rpt_needs_consent() {
		return ! $this->rpt_is_migrated() && $this->rpt_has_legacy_data();
	}

	/**
	 * Migrate legacy flat options into tab storage after admin consent.
	 *
	 * @return bool True on success, false on failure.
	 * @since 6.0.0
	 */
	public function rpt_run_migration() {
		if ( $this->rpt_is_migrated() ) {
			return true;
		}

		if ( ! $this->rpt_has_legacy_data() ) {
			return $this->rpt_initialize_fresh_storage();
		}

		$settings = $this->rpt_build_from_legacy();
		$this->rpt_write_tab_options( $settings );
		$this->rpt_mark_storage_migrated();
		$this->rpt_prune_legacy_storage();

		return true;
	}

	/**
	 * Write default tab options and mark storage as current (fresh install).
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	private function rpt_initialize_fresh_storage() {
		$this->rpt_write_tab_options( RPT_Settings_Schema::rpt_get_defaults() );
		$this->rpt_mark_storage_migrated();
		$this->rpt_prune_legacy_storage();

		return true;
	}

	/**
	 * Read rpt_settings_schema meta array.
	 *
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_get_schema_meta() {
		$meta = get_option( RPT_Settings_Schema::SCHEMA_META_OPTION, array() );

		return is_array( $meta ) ? $meta : array();
	}

	/**
	 * Read storage_version from rpt_settings_schema.
	 *
	 * @return int
	 * @since 6.0.0
	 */
	private function rpt_get_storage_version() {
		$meta = $this->rpt_get_schema_meta();

		return absint( $meta['storage_version'] ?? RPT_Settings_Schema::LEGACY_STORAGE_VERSION );
	}

	/**
	 * Whether legacy flat options were already removed for this site.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	private function rpt_is_legacy_pruned() {
		$meta = $this->rpt_get_schema_meta();

		return ! empty( $meta['legacy_pruned'] );
	}

	/**
	 * Record that legacy flat options were removed.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	private function rpt_mark_legacy_pruned() {
		$meta                      = $this->rpt_get_schema_meta();
		$meta['legacy_pruned']     = true;
		$meta['legacy_pruned_at']  = time();

		update_option(
			RPT_Settings_Schema::SCHEMA_META_OPTION,
			$meta,
			false
		);
	}

	/**
	 * Delete legacy and deprecated option rows, then mark pruning complete.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	private function rpt_prune_legacy_storage() {
		$this->rpt_cleanup_deprecated_storage();
		$this->rpt_delete_legacy_options();
		$this->rpt_mark_legacy_pruned();
	}

	/**
	 * Persist tab arrays to wp_options.
	 *
	 * @param array<string, array<string, mixed>> $settings Settings keyed by tab.
	 * @return void
	 * @since 6.0.0
	 */
	public function rpt_write_tab_options( array $settings ) {
		foreach ( RPT_Settings_Schema::rpt_get_tabs() as $tab ) {
			$tab_values = isset( $settings[ $tab ] ) && is_array( $settings[ $tab ] )
				? $settings[ $tab ]
				: array();

			update_option(
				RPT_Settings_Schema::rpt_get_tab_option_name( $tab ),
				$this->rpt_merge_tab_with_defaults( $tab, $tab_values ),
				RPT_Settings_Schema::rpt_tab_autoload()
			);
		}
	}

	/**
	 * Mark storage as migrated to the current version.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function rpt_mark_storage_migrated() {
		$meta                      = $this->rpt_get_schema_meta();
		$meta['storage_version']   = RPT_Settings_Schema::STORAGE_VERSION;
		$meta['migrated_at']       = time();

		update_option(
			RPT_Settings_Schema::SCHEMA_META_OPTION,
			$meta,
			false
		);
	}

	/**
	 * Remove orphan single-blob rows if they exist from local dev testing.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function rpt_cleanup_deprecated_storage() {
		delete_option( RPT_Settings_Schema::DEPRECATED_BLOB_OPTION );
		delete_option( RPT_Settings_Schema::DEPRECATED_MIGRATED_FLAG );
	}

	/**
	 * Delete legacy flat wp_options after successful consolidation.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function rpt_delete_legacy_options() {
		foreach ( RPT_Settings_Schema::rpt_get_legacy_option_keys() as $key ) {
			delete_option( $key );
		}
	}

	/**
	 * Whether any legacy flat option row exists.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public function rpt_has_legacy_data() {
		foreach ( RPT_Settings_Schema::rpt_get_legacy_option_keys() as $key ) {
			$value = get_option( $key, null );

			if ( null !== $value && false !== $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Build all tab settings from legacy flat options.
	 *
	 * @return array<string, array<string, mixed>>
	 * @since 6.0.0
	 */
	public function rpt_build_from_legacy() {
		$settings = RPT_Settings_Schema::rpt_get_defaults();
		$p        = $this->plugin;

		$settings['general']    = $this->rpt_build_legacy_general( $settings['general'], $p );
		$settings['thumbnails'] = $this->rpt_build_legacy_thumbnails( $settings['thumbnails'], $p );
		$settings['styles']     = $this->rpt_build_legacy_styles( $settings['styles'], $p );
		$settings['relations']  = $this->rpt_build_legacy_relations( $settings['relations'], $p );

		return $this->rpt_merge_with_defaults( $settings );
	}

	/**
	 * Map legacy options into the general tab.
	 *
	 * @param array<string, mixed>        $general Defaults for the general tab.
	 * @param RelatedPostsThumbnails|null $p       Plugin instance for class-property fallbacks.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_build_legacy_general( array $general, $p ) {
		$general['auto']           = $this->rpt_legacy_bool( 'relpoststh_auto', $p ? $p->auto : '1' );
		$general['dev_mode']       = $this->rpt_legacy_bool( 'relpoststh_devmode', $p ? $p->devmode : '0' );
		$general['cache_enabled']  = $this->rpt_legacy_bool( 'relpoststh_cache_enabled', '1' );
		$general['author_related'] = $this->rpt_legacy_bool( 'relpoststh_author_related', $p ? $p->author_related : '0' );
		$general['single_only']    = $this->rpt_legacy_bool( 'relpoststh_single_only', $p ? $p->single_only : '1' );
		$general['mobile_view']    = $this->rpt_legacy_bool( 'relpoststh_mobile_view', '0' );
		$general['show_taxonomy']  = $this->rpt_legacy_bool( 'relpoststh_show_taxonomy', '0' );
		$general['show_date']      = $this->rpt_legacy_bool( 'relpoststh_show_date', '0' );

		$general['post_types'] = $this->rpt_legacy_post_types(
			get_option( 'relpoststh_post_types', null ),
			$p ? $p->post_types : array( 'post' )
		);

		$categories_all_default = $this->rpt_legacy_bool(
			'relpoststh_categoriesall',
			$p ? $p->categories_all : '1'
		);

		$general['query_scope'] = $this->rpt_legacy_category_scope(
			get_option( 'relpoststh_categoriesall', null ),
			get_option( 'relpoststh_categories', null ),
			$categories_all_default
		);

		$general['display_scope'] = $this->rpt_build_legacy_display_scope( $general['query_scope'] );

		$general['start_date'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_startdate', null ),
			''
		);

		$general['post_sort'] = $this->rpt_legacy_post_sort(
			get_option( 'rpt_post_sort', null )
		);

		$top_text_data = $this->rpt_legacy_top_text_and_tag(
			get_option( 'relpoststh_top_text', null ),
			get_option( 'relpoststh_title_tag', null ),
			$p ? $p->top_text : ''
		);
		$general['top_text']  = $top_text_data['top_text'];
		$general['title_tag'] = $top_text_data['title_tag'];

		$general['number'] = $this->rpt_legacy_number(
			get_option( 'relpoststh_number', null ),
			$p ? (int) $p->number : 3
		);

		$general['default_image_type'] = $this->rpt_legacy_enum(
			get_option( 'relpoststh_default_image_type', null ),
			array( 'image', 'url' ),
			$p ? $p->default_image_type : 'image'
		);

		$general['default_image_url'] = esc_url_raw(
			$this->rpt_legacy_string( get_option( 'relpoststh_default_image_url', null ), '' )
		);

		$general['default_image'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_default_image', null ),
			$p ? (string) $p->default_image : ''
		);

		$general['date_format'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_date_format', null ),
			$p ? $p->format : 'j F, Y'
		);

		return $general;
	}

	/**
	 * Map legacy show-category options into display_scope.
	 *
	 * @param array<string, mixed> $query_scope Mapped query scope.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_build_legacy_display_scope( array $query_scope ) {
		$show_all_raw = get_option( 'relpoststh_show_categoriesall', null );
		$show_list    = get_option( 'relpoststh_show_categories', null );

		if ( null === $show_all_raw && null === $show_list ) {
			return $query_scope;
		}

		if ( null === $show_all_raw ) {
			$show_default = ( 'all' === $query_scope['mode'] ) ? '1' : '0';
		} else {
			$show_default = ( '1' === $this->rpt_legacy_yes_no_string( $show_all_raw, '1' ) ) ? '1' : '0';
		}

		$fallback_ids = ( null === $show_list )
			? $query_scope['category_ids']
			: $show_list;

		return $this->rpt_legacy_category_scope(
			$show_all_raw,
			$fallback_ids,
			$show_default
		);
	}

	/**
	 * Map legacy options into the thumbnails tab.
	 *
	 * @param array<string, mixed>        $thumbnails Defaults for the thumbnails tab.
	 * @param RelatedPostsThumbnails|null $p          Plugin instance for class-property fallbacks.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_build_legacy_thumbnails( array $thumbnails, $p ) {
		$thumbnails['thsource'] = $this->rpt_legacy_enum(
			get_option( 'relpoststh_thsource', null ),
			array( 'post-thumbnails', 'custom-field' ),
			$p ? $p->thsource : 'post-thumbnails'
		);

		$thumbnails['poststhname'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_poststhname', null ),
			$p ? $p->poststhname : 'thumbnail'
		);

		$thumbnails['only_with_thumbs']    = $this->rpt_legacy_bool( 'relpoststh_onlywiththumbs', '0' );
		$thumbnails['article_first_image'] = $this->rpt_legacy_bool( 'relpoststh_articlefirstimage', '1' );

		$thumbnails['image_type'] = $this->rpt_legacy_enum(
			get_option( 'relpoststh_customfield_type', null ),
			array( 'image', 'url' ),
			$p ? $p->customfield_type : 'image'
		);

		$thumbnails['customfield'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_customfield', null ),
			$p ? $p->custom_field : ''
		);

		$thumbnails['theme_resize_url'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_theme_resize_url', null ),
			''
		);

		$thumbnails['custom_width'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_customwidth', null ),
			$p ? $p->custom_width : '100'
		);

		$thumbnails['custom_height'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_customheight', null ),
			$p ? $p->custom_height : '100'
		);

		return $thumbnails;
	}

	/**
	 * Map legacy options into the styles tab.
	 *
	 * @param array<string, mixed>        $styles Defaults for the styles tab.
	 * @param RelatedPostsThumbnails|null $p      Plugin instance for class-property fallbacks.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_build_legacy_styles( array $styles, $p ) {
		$styles['output_style'] = $this->rpt_legacy_output_style(
			get_option( 'relpoststh_output_style', null ),
			$p ? $p->output_style : 'div'
		);

		$styles['clean_html'] = $this->rpt_legacy_bool( 'relpoststh_cleanhtml', '0' );

		$styles['column'] = $this->rpt_legacy_column(
			get_option( 'relpoststh_column', null ),
			$p ? $p->relpoststh_column : '3'
		);

		$styles['column_tablet'] = $this->rpt_legacy_column(
			get_option( 'relpoststh_column_t', null ),
			$p ? $p->relpoststh_column_t : '2'
		);

		$styles['column_mobile'] = $this->rpt_legacy_column(
			get_option( 'relpoststh_column_m', null ),
			$p ? $p->relpoststh_column_m : '2'
		);

		$styles['image_size'] = $this->rpt_legacy_aspect_ratio(
			get_option( 'relpoststh_image_size', null ),
			$p ? $p->relpoststh_image_size : '1/1'
		);

		$styles['background'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_background', null ),
			$p ? $p->background : '#ffffff'
		);

		$styles['hover_background'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_hoverbackground', null ),
			$p ? $p->hoverbackground : '#eeeeee'
		);

		$styles['border_color'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_bordercolor', null ),
			$p ? $p->border_color : '#dddddd'
		);

		$styles['font_color'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_fontcolor', null ),
			$p ? $p->font_color : '#333333'
		);

		$styles['font_family'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_fontfamily', null ),
			$p ? $p->font_family : 'Arial'
		);

		$styles['font_size'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_fontsize', null ),
			$p ? $p->font_size : '12'
		);

		$styles['spacing'] = $this->rpt_legacy_string(
			get_option( 'relpoststh_spacing', null ),
			'10px'
		);

		$styles['text_length'] = $this->rpt_legacy_length_string(
			get_option( 'relpoststh_textlength', null ),
			$p ? $p->text_length : '100'
		);

		$styles['excerpt_length'] = $this->rpt_legacy_length_string(
			get_option( 'relpoststh_excerptlength', null ),
			$p ? $p->excerpt_length : '0'
		);

		$styles['text_block_height'] = $this->rpt_legacy_length_string(
			get_option( 'relpoststh_textblockheight', null ),
			$p ? $p->text_block_height : '75'
		);

		return $styles;
	}

	/**
	 * Map legacy options into the relations tab.
	 *
	 * @param array<string, mixed>        $relations Defaults for the relations tab.
	 * @param RelatedPostsThumbnails|null $p         Plugin instance for class-property fallbacks.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_build_legacy_relations( array $relations, $p ) {
		$relations['relation'] = $this->rpt_legacy_enum(
			get_option( 'relpoststh_relation', null ),
			array( 'categories', 'tags', 'both', 'no', 'custom' ),
			$p ? $p->relation : 'categories'
		);

		$relations['custom_taxonomies'] = $this->rpt_legacy_string_array(
			get_option( 'relpoststh_custom_taxonomies', null ),
			$p ? $p->custom_taxonomies : array()
		);

		return $relations;
	}

	/**
	 * Merge partial nested settings onto tab defaults.
	 *
	 * @param array<string, array<string, mixed>> $settings Partial settings keyed by tab.
	 * @return array<string, array<string, mixed>>
	 * @since 6.0.0
	 */
	public function rpt_merge_with_defaults( array $settings ) {
		$merged = array();

		foreach ( RPT_Settings_Schema::rpt_get_tabs() as $tab ) {
			$tab_values = isset( $settings[ $tab ] ) && is_array( $settings[ $tab ] )
				? $settings[ $tab ]
				: array();

			$merged[ $tab ] = $this->rpt_merge_tab_with_defaults( $tab, $tab_values );
		}

		return $merged;
	}

	/**
	 * Merge one tab's values onto its defaults.
	 *
	 * @param string               $tab    Tab slug.
	 * @param array<string, mixed> $values Tab values.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	public function rpt_merge_tab_with_defaults( $tab, array $values ) {
		if ( 'styles' === $tab ) {
			$values = $this->rpt_normalize_renamed_style_keys( $values );
		}

		return array_replace_recursive(
			RPT_Settings_Schema::rpt_get_tab_defaults( $tab ),
			$values
		);
	}

	/**
	 * Upgrade short style keys from earlier 5.1 dev storage to full names.
	 *
	 * @param array<string, mixed> $values Styles tab values.
	 * @return array<string, mixed>
	 * @since  6.0.0
	 */
	private function rpt_normalize_renamed_style_keys( array $values ) {
		if ( isset( $values['column_t'] ) && ! isset( $values['column_tablet'] ) ) {
			$values['column_tablet'] = $values['column_t'];
		}
		unset( $values['column_t'] );

		if ( isset( $values['column_m'] ) && ! isset( $values['column_mobile'] ) ) {
			$values['column_mobile'] = $values['column_m'];
		}
		unset( $values['column_m'] );

		return $values;
	}

	/**
	 * Read a legacy yes/no wp_option as a boolean.
	 *
	 * @param string $option_name Legacy option key.
	 * @param string $default     Default "1" or "0" when the option row is missing.
	 * @return bool
	 * @since  6.0.0
	 */
	private function rpt_legacy_bool( $option_name, $default ) {
		return '1' === $this->rpt_legacy_yes_no_string( get_option( $option_name, null ), $default );
	}

	/**
	 * Normalize a legacy yes/no value to "1" or "0".
	 *
	 * @param mixed  $raw     Raw stored value, or null/false when missing.
	 * @param string $default Fallback when the option row is missing.
	 * @return string "1" or "0".
	 * @since  6.0.0
	 */
	private function rpt_legacy_yes_no_string( $raw, $default ) {
		if ( null === $raw || false === $raw ) {
			$raw = $default;
		}

		return '1' === (string) $raw ? '1' : '0';
	}

	/**
	 * Normalize a legacy scalar option to a string.
	 *
	 * @param mixed  $raw     Raw stored value, or null/false when missing.
	 * @param string $default Fallback when the option row is missing.
	 * @return string
	 * @since  6.0.0
	 */
	private function rpt_legacy_string( $raw, $default ) {
		if ( null === $raw || false === $raw ) {
			return (string) $default;
		}

		return (string) $raw;
	}

	/**
	 * Normalize a legacy length-style option, treating empty strings as missing.
	 *
	 * @param mixed  $raw     Raw stored value, or null/false when missing.
	 * @param string $default Fallback when the option row is missing or empty.
	 * @return string
	 * @since  6.0.0
	 */
	private function rpt_legacy_length_string( $raw, $default ) {
		if ( null === $raw || false === $raw || '' === (string) $raw ) {
			return (string) $default;
		}

		return (string) $raw;
	}

	/**
	 * Normalize legacy post type list storage.
	 *
	 * @param mixed $raw     Raw stored value, or null/false when missing.
	 * @param mixed $default Fallback post types when missing or empty after sanitize.
	 * @return array<int, string>
	 * @since  6.0.0
	 */
	private function rpt_legacy_post_types( $raw, $default ) {
		if ( null === $raw || false === $raw ) {
			$raw = $default;
		}

		$types = array_values(
			array_filter(
				array_map( 'sanitize_text_field', array_map( 'strval', (array) $raw ) )
			)
		);

		return empty( $types ) ? array( 'post' ) : $types;
	}

	/**
	 * Build a category scope object from legacy "all" flag and ID list options.
	 *
	 * @param mixed  $all_raw     Legacy categoriesall / show_categoriesall value.
	 * @param mixed  $ids_raw     Legacy categories / show_categories value.
	 * @param string $default_all Default "1" or "0" for the all-categories flag.
	 * @return array{mode: string, category_ids: array<int, string>}
	 * @since  6.0.0
	 */
	private function rpt_legacy_category_scope( $all_raw, $ids_raw, $default_all ) {
		if ( '1' === $this->rpt_legacy_yes_no_string( $all_raw, $default_all ) ) {
			return array(
				'mode'         => 'all',
				'category_ids' => array(),
			);
		}

		return array(
			'mode'         => 'selected',
			'category_ids' => $this->rpt_legacy_string_array( $ids_raw, array() ),
		);
	}

	/**
	 * Normalize a legacy string array option.
	 *
	 * @param mixed $raw     Raw stored value, or null/false when missing.
	 * @param mixed $default Fallback array when missing.
	 * @return array<int, string>
	 * @since  6.0.0
	 */
	private function rpt_legacy_string_array( $raw, $default ) {
		if ( null === $raw || false === $raw ) {
			$raw = $default;
		}

		if ( ! is_array( $raw ) ) {
			return array();
		}

		return array_values(
			array_filter(
				array_map( 'strval', array_map( 'sanitize_text_field', $raw ) )
			)
		);
	}

	/**
	 * Normalize legacy post sort storage to rand or latest.
	 *
	 * @param mixed $raw Raw stored value, or null/false when missing.
	 * @return string "rand" or "latest".
	 * @since  6.0.0
	 */
	private function rpt_legacy_post_sort( $raw ) {
		$sort = $this->rpt_legacy_string( $raw, 'rand' );

		return in_array( $sort, array( 'rand', 'latest' ), true ) ? $sort : 'rand';
	}

	/**
	 * Map legacy output_style values to block or list storage.
	 *
	 * Legacy "div" and empty values become "block"; "list" is preserved.
	 *
	 * @param mixed  $raw            Raw stored value, or null/false when missing.
	 * @param string $class_default  Plugin class default when missing.
	 * @return string "block" or "list".
	 * @since  6.0.0
	 */
	private function rpt_legacy_output_style( $raw, $class_default ) {
		if ( null === $raw || false === $raw ) {
			$raw = $class_default;
		}

		$style = sanitize_text_field( (string) $raw );

		if ( '' === $style || 'div' === $style || 'block' === $style ) {
			return 'block';
		}

		return 'list' === $style ? 'list' : 'block';
	}

	/**
	 * Normalize a legacy column count option.
	 *
	 * @param mixed  $raw     Raw stored value, or null/false when missing.
	 * @param string $default Fallback when missing or empty.
	 * @return string
	 * @since  6.0.0
	 */
	private function rpt_legacy_column( $raw, $default ) {
		$value = $this->rpt_legacy_string( $raw, $default );

		return '' === $value ? (string) $default : $value;
	}

	/**
	 * Normalize a legacy aspect ratio option to an allowed value.
	 *
	 * @param mixed  $raw     Raw stored value, or null/false when missing.
	 * @param string $default Fallback when missing, empty, or invalid.
	 * @return string
	 * @since  6.0.0
	 */
	private function rpt_legacy_aspect_ratio( $raw, $default ) {
		$value = $this->rpt_legacy_string( $raw, $default );

		if ( '' === $value ) {
			return (string) $default;
		}

		$allowed = array( '16/9', '4/3', '1/1', '3/2', '21/9', '9/16' );

		return in_array( $value, $allowed, true ) ? $value : (string) $default;
	}

	/**
	 * Normalize a legacy enum option against an allow-list.
	 *
	 * @param mixed               $raw     Raw stored value, or null/false when missing.
	 * @param array<int, string>  $allowed Allowed stored values.
	 * @param string              $default Fallback when missing or invalid.
	 * @return string
	 * @since  6.0.0
	 */
	private function rpt_legacy_enum( $raw, array $allowed, $default ) {
		$value = $this->rpt_legacy_string( $raw, $default );

		return in_array( $value, $allowed, true ) ? $value : (string) $default;
	}

	/**
	 * Normalize legacy related-post count storage.
	 *
	 * @param mixed $raw     Raw stored value, or null/false when missing.
	 * @param int   $default Fallback when missing or zero after cast.
	 * @return int Positive integer, minimum 1.
	 * @since  6.0.0
	 */
	private function rpt_legacy_number( $raw, $default ) {
		if ( null === $raw || false === $raw || '' === (string) $raw ) {
			return max( 1, (int) $default );
		}

		$number = absint( $raw );

		if ( $number <= 0 ) {
			return 3;
		}

		return min( RPT_Settings_Schema::MAX_POSTS_NUMBER, $number );
	}

	/**
	 * Split legacy top text HTML into plain text and title tag for storage.
	 *
	 * Legacy sites may store wrapped HTML (e.g. `<h3>Related posts:</h3>`).
	 * This extracts inner text and the wrapper tag when present.
	 *
	 * @param mixed  $top_text_raw   Raw relpoststh_top_text value.
	 * @param mixed  $title_tag_raw  Raw relpoststh_title_tag value.
	 * @param string $class_default  Plugin class default top text when missing.
	 * @return array{top_text: string, title_tag: string}
	 * @since  6.0.0
	 */
	private function rpt_legacy_top_text_and_tag( $top_text_raw, $title_tag_raw, $class_default ) {
		$allowed_tags = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'strong' );
		$title_tag    = $this->rpt_legacy_enum( $title_tag_raw, $allowed_tags, 'h2' );

		if ( null === $top_text_raw || false === $top_text_raw ) {
			$fallback = is_string( $class_default ) ? trim( $class_default ) : '';

			return array(
				'top_text'  => '' !== $fallback ? wp_strip_all_tags( stripslashes( $fallback ) ) : '',
				'title_tag' => $title_tag,
			);
		}

		$stored = stripslashes( (string) $top_text_raw );

		if ( '' === trim( $stored ) ) {
			return array(
				'top_text'  => '',
				'title_tag' => $title_tag,
			);
		}

		if ( preg_match( '/^\s*<([a-z0-9]+)[^>]*>(.*?)<\/\1>\s*$/is', $stored, $matches ) ) {
			$tag = strtolower( $matches[1] );

			if ( in_array( $tag, $allowed_tags, true ) ) {
				return array(
					'top_text'  => trim( wp_strip_all_tags( $matches[2] ) ),
					'title_tag' => $tag,
				);
			}
		}

		return array(
			'top_text'  => trim( wp_strip_all_tags( $stored ) ),
			'title_tag' => $title_tag,
		);
	}
}
