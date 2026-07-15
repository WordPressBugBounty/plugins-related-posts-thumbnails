<?php
/**
 * Frontend asset registration and conditional loading for Related Posts Thumbnails.
 *
 * @package Related_Posts_Thumbnails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles frontend CSS/JS registration, conditional enqueue, and defer loading.
 *
 * @since 5.0.0
 */
class RPT_Frontend_Assets {

	const RPT_STYLE_HANDLE  = 'rpt-front';
	const RPT_SCRIPT_HANDLE = 'rpt-lazy-load';

	/**
	 * Whether frontend assets have been enqueued.
	 *
	 * @var bool
	 */
	private static $enqueued = false;

	/**
	 * Main plugin file path for asset URLs.
	 *
	 * @var string
	 */
	private static $plugin_file = '';

	/**
	 * Bootstrap asset hooks.
	 *
	 * @param string $plugin_file Main plugin file path.
	 * @return void
	 * @since 5.0.0
	 */
	public static function rpt_init( $plugin_file ) {
		self::$plugin_file = $plugin_file;

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'rpt_register_assets' ), 5 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'rpt_maybe_enqueue_early' ), 20 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'rpt_defer_script_tag' ), 10, 3 );
		add_filter( 'style_loader_tag', array( __CLASS__, 'rpt_async_style_tag' ), 10, 4 );
	}

	/**
	 * Register frontend styles and scripts without enqueuing them.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public static function rpt_register_assets() {
		if ( is_admin() ) {
			return;
		}

		wp_register_style(
			self::RPT_STYLE_HANDLE,
			self::rpt_get_asset_url( 'assets/css/front.css', 'assets/css/front.min.css' ),
			array(),
			RELATED_POSTS_THUMBNAILS_VERSION
		);

		wp_register_script(
			self::RPT_SCRIPT_HANDLE,
			self::rpt_get_asset_url( 'assets/js/lazy-load.js', 'assets/js/lazy-load.min.js' ),
			array(),
			RELATED_POSTS_THUMBNAILS_VERSION,
			true
		);
	}

	/**
	 * Resolve a frontend asset URL, preferring minified builds when available.
	 *
	 * @param string $source_relative_path Source asset path relative to the plugin root.
	 * @param string $min_relative_path    Minified asset path relative to the plugin root.
	 * @return string
	 * @since 5.0.0
	 */
	private static function rpt_get_asset_url( $source_relative_path, $min_relative_path ) {
		$use_minified  = ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG );
		$relative_path = $source_relative_path;

		if ( $use_minified ) {
			$min_file_path = plugin_dir_path( self::$plugin_file ) . $min_relative_path;

			if ( file_exists( $min_file_path ) ) {
				$relative_path = $min_relative_path;
			}
		}

		return plugins_url( $relative_path, self::$plugin_file );
	}

	/**
	 * Enqueue assets when the current request is likely to render related posts.
	 *
	 * @return void
	 * @since 5.0.0
	 */
	public static function rpt_maybe_enqueue_early() {
		if ( self::rpt_should_load_on_request() ) {
			self::rpt_enqueue();
		}
	}

	/**
	 * Enqueue frontend assets once.
	 *
	 * @param RelatedPostsThumbnails|null $plugin Plugin instance for dynamic CSS defaults.
	 * @return void
	 * @since 5.0.0
	 */
	public static function rpt_enqueue( $plugin = null ) {
		if ( self::$enqueued || is_admin() || self::rpt_is_amp_context() ) {
			return;
		}

		self::rpt_register_assets();

		wp_enqueue_style( self::RPT_STYLE_HANDLE );
		wp_add_inline_style( self::RPT_STYLE_HANDLE, self::rpt_get_dynamic_css( $plugin ) );
		wp_enqueue_script( self::RPT_SCRIPT_HANDLE );

		self::$enqueued = true;
	}

	/**
	 * Determine whether related posts assets should load on the current request.
	 *
	 * Covers: sidebar widget, shortcode, Gutenberg block, and auto-append.
	 * Custom theme/plugin display can force early loading via the
	 * `rpt_should_load_frontend_assets` filter. Late enqueue still runs from
	 * get_thumbnails() when related posts HTML is actually rendered.
	 *
	 * @return bool
	 * @since 5.0.0
	 */
	public static function rpt_should_load_on_request() {
		if ( is_admin() || self::rpt_is_amp_context() || self::rpt_is_amp_output_disabled() ) {
			return false;
		}

		$should_load = false;

		if ( self::rpt_is_widget_active_on_request() ) {
			$should_load = true;
		} elseif ( is_singular() && self::rpt_post_has_rpt_markup( get_post() ) ) {
			$should_load = true;
		} else {
			$should_load = self::rpt_is_auto_append_active();
		}

		/**
		 * Filter whether RPT frontend assets should load on this request.
		 *
		 * @since 5.0.0
		 *
		 * @param bool $should_load Whether assets should load.
		 */
		return (bool) apply_filters( 'rpt_should_load_frontend_assets', $should_load );
	}

	/**
	 * Check whether the sidebar widget is active on the current view.
	 *
	 * @return bool
	 * @since 5.0.0
	 */
	private static function rpt_is_widget_active_on_request() {
		if ( ! is_single() || is_page() ) {
			return false;
		}

		return (bool) is_active_widget( false, false, 'relatedpoststhumbnailswidget', true );
	}

	/**
	 * Check whether the current post contains RPT shortcode or block markup.
	 *
	 * @param WP_Post|null $post Post object.
	 * @return bool
	 * @since 5.0.0
	 */
	private static function rpt_post_has_rpt_markup( $post ) {
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( has_shortcode( $post->post_content, 'related-posts-thumbnails' ) ) {
			return true;
		}

		if ( function_exists( 'has_block' ) && has_block( 'related-post-thumbnails/rpt-block', $post ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check whether auto-append is enabled and allowed on the current request.
	 *
	 * @return bool
	 * @since 5.0.0
	 */
	private static function rpt_is_auto_append_active() {
		global $related_posts_thumbnails;

		if ( ! $related_posts_thumbnails instanceof RelatedPostsThumbnails ) {
			return false;
		}

		if ( ! get_option( 'relpoststh_auto', $related_posts_thumbnails->auto ) ) {
			return false;
		}

		if ( wp_is_mobile() && '1' === get_option( 'relpoststh_mobile_view', '0' ) ) {
			return false;
		}

		if ( $related_posts_thumbnails->prevent_on_editors() ) {
			return false;
		}

		return $related_posts_thumbnails->is_relpoststh_show();
	}

	/**
	 * Build dynamic CSS previously output in wp_head.
	 *
	 * @param RelatedPostsThumbnails|null $plugin Plugin instance.
	 * @return string
	 * @since 5.0.0
	 */
	public static function rpt_get_dynamic_css( $plugin = null ) {
		global $related_posts_thumbnails;

		if ( ! $plugin instanceof RelatedPostsThumbnails ) {
			$plugin = $related_posts_thumbnails;
		}

		if ( ! $plugin instanceof RelatedPostsThumbnails ) {
			return '';
		}

		$border_color    = esc_attr( get_option( 'relpoststh_bordercolor', $plugin->border_color ) );
		$background      = esc_attr( get_option( 'relpoststh_background', $plugin->background ) );
		$hoverbackground = esc_attr( get_option( 'relpoststh_hoverbackground', $plugin->hoverbackground ) );
		$font_size       = esc_attr( get_option( 'relpoststh_fontsize', $plugin->font_size ) );
		$font_color      = esc_attr( get_option( 'relpoststh_fontcolor', $plugin->font_color ) );
		$spacing         = esc_attr( get_option( 'relpoststh_spacing', '10px' ) );

		return "#related_posts_thumbnails li{border-right:1px solid {$border_color};background-color:{$background}}"
			. "#related_posts_thumbnails li:hover{background-color:{$hoverbackground}}"
			. ".relpost_content{font-size:{$font_size}px;color:{$font_color}}"
			. ".relpost-block-single{background-color:{$background};border-right:1px solid {$border_color};border-left:1px solid {$border_color};margin-right:-1px}"
			. ".relpost-block-single:hover{background-color:{$hoverbackground}}"
			. ".relpost-block-single-image,.relpost-post-image{margin-bottom:{$spacing}}";
	}

	/**
	 * Add defer to the lazy-load script tag.
	 *
	 * @param string $tag    Script tag HTML.
	 * @param string $handle Script handle.
	 * @param string $src    Script source URL.
	 * @return string
	 * @since 5.0.0
	 */
	public static function rpt_defer_script_tag( $tag, $handle, $src ) {
		if ( self::RPT_SCRIPT_HANDLE !== $handle || false !== strpos( $tag, ' defer' ) ) {
			return $tag;
		}

		return str_replace( ' src=', ' defer src=', $tag );
	}

	/**
	 * Load frontend CSS asynchronously to avoid render blocking.
	 *
	 * @param string $html   Link tag HTML.
	 * @param string $handle Style handle.
	 * @param string $href   Style URL.
	 * @param string $media  Media attribute.
	 * @return string
	 * @since 5.0.0
	 */
	public static function rpt_async_style_tag( $html, $handle, $href, $media ) {
		if ( self::RPT_STYLE_HANDLE !== $handle || ! self::$enqueued ) {
			return $html;
		}

		$html = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
		$html = str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $html );

		return $html;
	}

	/**
	 * Whether the current request is an AMP endpoint.
	 *
	 * @return bool
	 * @since 5.0.0
	 */
	private static function rpt_is_amp_context() {
		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}

	/**
	 * Whether RPT output is disabled on AMP pages.
	 *
	 * @return bool
	 * @since 5.0.0
	 */
	private static function rpt_is_amp_output_disabled() {
		return self::rpt_is_amp_context() && 'disable' === apply_filters( 'rpth_amp', true );
	}
}
