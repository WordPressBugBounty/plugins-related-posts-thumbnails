<?php
/**
 * Settings schema, tab option keys, defaults, and legacy option registry.
 *
 * Storage layout (storage_version 1):
 * - rpt_settings_schema          (autoload: no)  migration meta (storage_version, legacy_pruned, …)
 * - rpt_settings_general         (autoload: yes)
 * - rpt_settings_thumbnails      (autoload: yes)
 * - rpt_settings_styles          (autoload: yes)
 * - rpt_settings_relations       (autoload: yes)
 *
 * Tab arrays hold user settings only (no schema_version inside tabs).
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings schema definition and legacy key registry.
 *
 * @since 6.0.0
 */
class RPT_Settings_Schema {

	/**
	 * Migration / storage version meta (not user settings).
	 */
	const SCHEMA_META_OPTION = 'rpt_settings_schema';

	/**
	 * Legacy flat relpoststh_* options (storage_version 0).
	 */
	const LEGACY_STORAGE_VERSION = 0;

	/**
	 * Four tab options (current storage_version).
	 */
	const STORAGE_VERSION = 1;

	/**
	 * Maximum related posts count allowed in settings.
	 */
	const MAX_POSTS_NUMBER = 50;

	/**
	 * Orphan options deleted after migration (unused dev-only rows).
	 */
	const DEPRECATED_BLOB_OPTION = 'rpt_settings';

	/**
	 * Orphan flag deleted after migration (unused dev-only row).
	 */
	const DEPRECATED_MIGRATED_FLAG = 'rpt_settings_migrated';

	/**
	 * Tab slugs used in code and REST.
	 *
	 * @return array<int, string>
	 * @since 6.0.0
	 */
	public static function rpt_get_tabs() {
		return array( 'general', 'thumbnails', 'styles', 'relations' );
	}

	/**
	 * wp_options key for a settings tab.
	 *
	 * @param string $tab Tab slug.
	 * @return string
	 * @since 6.0.0
	 */
	public static function rpt_get_tab_option_name( $tab ) {
		return 'rpt_settings_' . sanitize_key( $tab );
	}

	/**
	 * Whether tab options are autoloaded.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public static function rpt_tab_autoload() {
		return true;
	}

	/**
	 * Legacy option keys merged into tab options on migration.
	 *
	 * @return array<int, string>
	 * @since 6.0.0
	 */
	public static function rpt_get_legacy_option_keys() {
		return array(
			'relpoststh_auto',
			'relpoststh_devmode',
			'relpoststh_cache_enabled',
			'relpoststh_author_related',
			'relpoststh_single_only',
			'relpoststh_mobile_view',
			'relpoststh_post_types',
			'relpoststh_categoriesall',
			'relpoststh_categories',
			'relpoststh_show_categoriesall',
			'relpoststh_show_categories',
			'relpoststh_show_taxonomy',
			'relpoststh_startdate',
			'rpt_post_sort',
			'relpoststh_top_text',
			'relpoststh_title_tag',
			'relpoststh_number',
			'relpoststh_default_image_type',
			'relpoststh_default_image_url',
			'relpoststh_default_image',
			'relpoststh_show_date',
			'relpoststh_date_format',
			'relpoststh_thsource',
			'relpoststh_poststhname',
			'relpoststh_onlywiththumbs',
			'relpoststh_articlefirstimage',
			'relpoststh_customfield_type',
			'relpoststh_customfield',
			'relpoststh_theme_resize_url',
			'relpoststh_customwidth',
			'relpoststh_customheight',
			'relpoststh_output_style',
			'relpoststh_cleanhtml',
			'relpoststh_column',
			'relpoststh_column_t',
			'relpoststh_column_m',
			'relpoststh_image_size',
			'relpoststh_background',
			'relpoststh_hoverbackground',
			'relpoststh_bordercolor',
			'relpoststh_fontcolor',
			'relpoststh_fontfamily',
			'relpoststh_fontsize',
			'relpoststh_spacing',
			'relpoststh_textlength',
			'relpoststh_excerptlength',
			'relpoststh_textblockheight',
			'relpoststh_relation',
			'relpoststh_custom_taxonomies',
		);
	}

	/**
	 * Default values for all tabs (nested by tab slug).
	 *
	 * @return array<string, array<string, mixed>>
	 * @since 6.0.0
	 */
	public static function rpt_get_defaults() {
		return array(
			'general'    => self::rpt_get_tab_defaults( 'general' ),
			'thumbnails' => self::rpt_get_tab_defaults( 'thumbnails' ),
			'styles'     => self::rpt_get_tab_defaults( 'styles' ),
			'relations'  => self::rpt_get_tab_defaults( 'relations' ),
		);
	}

	/**
	 * Default values for one tab.
	 *
	 * @param string $tab Tab slug.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	public static function rpt_get_tab_defaults( $tab ) {
		switch ( $tab ) {
			case 'general':
				return array(
					'auto'                => true,
					'dev_mode'            => false,
					'cache_enabled'       => true,
					'author_related'      => false,
					'single_only'         => true,
					'mobile_view'         => false,
					'post_types'          => array( 'post' ),
					'query_scope'         => array(
						'mode'         => 'all',
						'category_ids' => array(),
					),
					'display_scope'       => array(
						'mode'         => 'all',
						'category_ids' => array(),
					),
					'show_taxonomy'       => false,
					'start_date'          => '',
					'post_sort'           => 'rand',
					'top_text'            => '',
					'title_tag'           => 'h2',
					'number'              => 3,
					'default_image_type'  => 'image',
					'default_image_url'   => '',
					'default_image'       => '',
					'show_date'           => false,
					'date_format'         => 'j F, Y',
				);

			case 'thumbnails':
				return array(
					'thsource'            => 'post-thumbnails',
					'poststhname'         => 'thumbnail',
					'only_with_thumbs'    => false,
					'article_first_image' => true,
					'image_type'          => 'image',
					'customfield'         => '',
					'theme_resize_url'    => '',
					'custom_width'        => '100',
					'custom_height'       => '100',
				);

			case 'styles':
				return array(
					'output_style'      => 'block',
					'clean_html'        => false,
					'column'            => '3',
					'column_tablet'     => '2',
					'column_mobile'     => '2',
					'image_size'        => '1/1',
					'background'        => '#ffffff',
					'hover_background'  => '#eeeeee',
					'border_color'      => '#dddddd',
					'font_color'        => '#333333',
					'font_family'       => 'Arial',
					'font_size'         => '12',
					'spacing'           => '10px',
					'text_length'       => '100',
					'excerpt_length'    => '0',
					'text_block_height' => '75',
				);

			case 'relations':
				return array(
					'relation'          => 'categories',
					'custom_taxonomies' => array(),
				);

			default:
				return array();
		}
	}
}
