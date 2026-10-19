<?php
/**
 * Helper functions for tab-based rpt_settings access.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'rpt_settings_bool' ) ) {
	/**
	 * Read a boolean setting by dot path.
	 *
	 * @param string $path    Dot path, e.g. general.auto.
	 * @param bool   $default Default when missing.
	 * @return bool
	 * @since  6.0.0
	 */
	function rpt_settings_bool( $path, $default = false ) {
		$value = rpt_settings()->rpt_get( $path, $default );

		if ( is_bool( $value ) ) {
			return $value;
		}

		return '1' === (string) $value || 1 === $value || true === $value;
	}
}

if ( ! function_exists( 'rpt_settings_yes_no' ) ) {
	/**
	 * Read a boolean setting as legacy "1" / "0" for React and templates.
	 *
	 * @param string $path           Dot path.
	 * @param string $default_yes_no Default "1" or "0".
	 * @return string
	 * @since  6.0.0
	 */
	function rpt_settings_yes_no( $path, $default_yes_no = '0' ) {
		$default = '1' === (string) $default_yes_no;

		return rpt_settings_bool( $path, $default ) ? '1' : '0';
	}
}

if ( ! function_exists( 'rpt_settings_article_first_image_enabled' ) ) {
	/**
	 * Whether first in-post images may be used as thumbnails.
	 *
	 * Disabled when "only posts with featured image" is enabled.
	 *
	 * @param bool $default Default when article_first_image is missing.
	 * @return bool
	 * @since  6.0.0
	 */
	function rpt_settings_article_first_image_enabled( $default = true ) {
		if ( rpt_settings_bool( 'thumbnails.only_with_thumbs' ) ) {
			return false;
		}

		return rpt_settings_bool( 'thumbnails.article_first_image', $default );
	}
}

if ( ! function_exists( 'rpt_settings_scope_is_all' ) ) {
	/**
	 * Whether a category scope object means "all categories".
	 *
	 * @param mixed $scope Scope array with mode + category_ids.
	 * @return bool
	 * @since  6.0.0
	 */
	function rpt_settings_scope_is_all( $scope ) {
		return is_array( $scope ) && ( $scope['mode'] ?? 'all' ) === 'all';
	}
}

if ( ! function_exists( 'rpt_settings_scope_ids' ) ) {
	/**
	 * Category IDs from a scope object.
	 *
	 * @param mixed $scope Scope array.
	 * @return array<int, string>
	 * @since  6.0.0
	 */
	function rpt_settings_scope_ids( $scope ) {
		if ( ! is_array( $scope ) || ! isset( $scope['category_ids'] ) ) {
			return array();
		}

		return array_values( array_map( 'strval', (array) $scope['category_ids'] ) );
	}
}

if ( ! function_exists( 'rpt_settings_rest_scope_to_storage' ) ) {
	/**
	 * Convert React multi-select scope payload to storage shape.
	 *
	 * @param mixed $raw_value REST field value (array with "0" = all, or legacy "1"/"0").
	 * @return array{mode: string, category_ids: array<int, string>}
	 * @since  6.0.0
	 */
	function rpt_settings_rest_scope_to_storage( $raw_value ) {
		if ( is_array( $raw_value ) ) {
			if ( in_array( '0', $raw_value, true ) || empty( $raw_value ) ) {
				return array(
					'mode'         => 'all',
					'category_ids' => array(),
				);
			}

			return array(
				'mode'         => 'selected',
				'category_ids' => array_values(
					array_map( 'strval', array_map( 'sanitize_text_field', $raw_value ) )
				),
			);
		}

		if ( '1' === (string) $raw_value || true === $raw_value ) {
			return array(
				'mode'         => 'all',
				'category_ids' => array(),
			);
		}

		return array(
			'mode'         => 'selected',
			'category_ids' => array(),
		);
	}
}

if ( ! function_exists( 'rpt_settings_format_top_text_html' ) ) {
	/**
	 * Render stored plain top text with the configured title tag.
	 *
	 * @param string $top_text  Plain heading text.
	 * @param string $title_tag Allowed wrapper tag.
	 * @return string
	 * @since  6.0.0
	 */
	function rpt_settings_format_top_text_html( $top_text, $title_tag = 'h2' ) {
		$top_text  = trim( wp_strip_all_tags( (string) $top_text ) );
		$title_tag = sanitize_key( (string) $title_tag );
		$allowed   = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'strong' );

		if ( '' === $top_text ) {
			return '';
		}

		if ( ! in_array( $title_tag, $allowed, true ) ) {
			$title_tag = 'h2';
		}

		return sprintf(
			'<%1$s class="relpoststh-block-title">%2$s</%1$s>',
			$title_tag,
			esc_html( $top_text )
		);
	}
}

if ( ! function_exists( 'rpt_get_yes_no_option' ) ) {
	/**
	 * Read a plugin yes/no setting as "1" or "0".
	 *
	 * Maps legacy option names to rpt_settings paths for backward compatibility.
	 *
	 * @param string $option_name Legacy option key.
	 * @param string $default     Default when unset.
	 * @return string "1" or "0".
	 * @since  5.0.1
	 */
	function rpt_get_yes_no_option( $option_name, $default = '0' ) {
		$path = rpt_settings_legacy_path( $option_name );

		if ( null === $path ) {
			return '1' === (string) $default ? '1' : '0';
		}

		if ( 0 === strpos( $path, 'scope:' ) ) {
			$scope_path = substr( $path, 6 );

			return rpt_settings_scope_is_all( rpt_settings()->rpt_get( $scope_path, array() ) ) ? '1' : '0';
		}

		return rpt_settings_yes_no( $path, $default );
	}
}

if ( ! function_exists( 'rpt_is_yes_option' ) ) {
	/**
	 * Whether a legacy yes/no setting is enabled.
	 *
	 * @param string $option_name Legacy option key.
	 * @param string $default     Default when unset.
	 * @return bool
	 * @since  5.0.1
	 */
	function rpt_is_yes_option( $option_name, $default = '0' ) {
		return '1' === rpt_get_yes_no_option( $option_name, $default );
	}
}

if ( ! function_exists( 'rpt_settings_legacy_path' ) ) {
	/**
	 * Map a legacy wp_options key to an rpt_settings dot path.
	 *
	 * @param string $legacy_key Legacy option name.
	 * @return string|null Dot path, scope:path for category scopes, or null.
	 * @since  6.0.0
	 */
	function rpt_settings_legacy_path( $legacy_key ) {
		static $map = null;

		if ( null === $map ) {
			$map = array(
				'relpoststh_auto'              => 'general.auto',
				'relpoststh_devmode'           => 'general.dev_mode',
				'relpoststh_cache_enabled'     => 'general.cache_enabled',
				'relpoststh_author_related'    => 'general.author_related',
				'relpoststh_single_only'       => 'general.single_only',
				'relpoststh_mobile_view'       => 'general.mobile_view',
				'relpoststh_show_taxonomy'     => 'general.show_taxonomy',
				'relpoststh_show_date'         => 'general.show_date',
				'relpoststh_onlywiththumbs'    => 'thumbnails.only_with_thumbs',
				'relpoststh_articlefirstimage' => 'thumbnails.article_first_image',
				'relpoststh_cleanhtml'         => 'styles.clean_html',
				'relpoststh_categoriesall'     => 'scope:general.query_scope',
				'relpoststh_show_categoriesall' => 'scope:general.display_scope',
				'relpoststh_post_types'        => 'general.post_types',
				'relpoststh_categories'        => 'general.query_scope.category_ids',
				'relpoststh_show_categories'   => 'general.display_scope.category_ids',
				'relpoststh_startdate'         => 'general.start_date',
				'rpt_post_sort'                => 'general.post_sort',
				'relpoststh_top_text'          => 'general.top_text',
				'relpoststh_title_tag'         => 'general.title_tag',
				'relpoststh_number'            => 'general.number',
				'relpoststh_default_image_type' => 'general.default_image_type',
				'relpoststh_default_image_url' => 'general.default_image_url',
				'relpoststh_default_image'     => 'general.default_image',
				'relpoststh_date_format'       => 'general.date_format',
				'relpoststh_thsource'          => 'thumbnails.thsource',
				'relpoststh_poststhname'       => 'thumbnails.poststhname',
				'relpoststh_customfield_type'  => 'thumbnails.image_type',
				'relpoststh_customfield'       => 'thumbnails.customfield',
				'relpoststh_theme_resize_url'  => 'thumbnails.theme_resize_url',
				'relpoststh_customwidth'       => 'thumbnails.custom_width',
				'relpoststh_customheight'      => 'thumbnails.custom_height',
				'relpoststh_output_style'      => 'styles.output_style',
				'relpoststh_column'            => 'styles.column',
				'relpoststh_column_t'          => 'styles.column_tablet',
				'relpoststh_column_m'          => 'styles.column_mobile',
				'relpoststh_image_size'        => 'styles.image_size',
				'relpoststh_background'        => 'styles.background',
				'relpoststh_hoverbackground'   => 'styles.hover_background',
				'relpoststh_bordercolor'       => 'styles.border_color',
				'relpoststh_fontcolor'         => 'styles.font_color',
				'relpoststh_fontfamily'        => 'styles.font_family',
				'relpoststh_fontsize'          => 'styles.font_size',
				'relpoststh_spacing'           => 'styles.spacing',
				'relpoststh_textlength'        => 'styles.text_length',
				'relpoststh_excerptlength'     => 'styles.excerpt_length',
				'relpoststh_textblockheight'   => 'styles.text_block_height',
				'relpoststh_relation'          => 'relations.relation',
				'relpoststh_custom_taxonomies' => 'relations.custom_taxonomies',
			);
		}

		return $map[ $legacy_key ] ?? null;
	}
}

if ( ! function_exists( 'rpt_is_mobile_hidden' ) ) {
	/**
	 * Whether related posts output should be hidden for the current mobile request.
	 *
	 * @return bool
	 * @since  5.0.1
	 */
	function rpt_is_mobile_hidden() {
		return rpt_settings_bool( 'general.mobile_view' ) && wp_is_mobile();
	}
}
