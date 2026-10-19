<?php
/**
 * Plugin Name:  Related Posts Thumbnails
 * Plugin URI:   https://wpbrigade.com/wordpress/plugins/related-posts/?utm_source=related-posts-lite&utm_medium=plugin-uri&utm_campaign=pro-upgrade-rp
 * Description:  Showing related posts thumbnails under the posts.
 * Version:           6.0.0
 * Requires at least: 5.3
 * Requires PHP:      7.4
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       related-posts-thumbnails
 * Author:       WPBrigade
 * Author URI:   https://WPBrigade.com/?utm_source=related-posts-lite&utm_medium=author-link&utm_campaign=pro-upgrade-rp
 * GitHub Plugin URI: https://github.com/WPBrigade/related-posts-thumbnails
 */

/*
Copyright 2010 - 2026 WPBrigade.com

This product was first developed by Maria I Shaldybina and later on maintained and developed by Adnan (WPBrigade.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'rpt_wpb92640233' ) ) {
	/**
	 * Create a helper function for easy SDK access.
	 *
	 * @return mixed
	 */
	function rpt_wpb92640233() {
		global $rpt_wpb92640233;

		if ( ! isset( $rpt_wpb92640233 ) || ! is_array( $rpt_wpb92640233 ) ) {
			require_once __DIR__ . '/lib/wpb-sdk/start.php';

			/**
			 * Initialize WPB SDK.
			 *
			 * @phpstan-ignore-next-line
			 */
			$rpt_wpb92640233 = wpb_sdk_dynamic_init(
				array(
					'id'              => '3',
					'slug'            => 'related-posts-thumbnails',
					'type'            => 'plugin',
					'plugin_file'     => __FILE__,
					'sdk_views_dir'   => __DIR__ . '/lib/wpb-sdk/views',
					'public_key'      => '1|4aOA8EuyIN4pi2miMvC23LLpnHbBZFNki9R9pVmwd673d3c8', // NOSONAR — WPB SDK product credential (php:S6418)
					'secret_key'      => 'sk_b36c525848fee035', // NOSONAR — WPB SDK product credential (php:S6418)
					'is_premium'      => false,
					'has_addons'      => false,
					'has_paid_plans'  => false,
					'optin_user_meta' => array(
						'token'          => '_rpt_verification_token',
						'email_verified' => '_rpt_email_verified',
					),
					'optin'           => array(
						'option_name'       => '_rpt_optin',
						'settings_page'     => 'related-posts-thumbnails',
						'optin_page'        => 'rpt-optin',
						'logo_path'         => 'assets/images/rpt-brand.svg',
						'verify_query_args' => array(
							'related-posts-thumbnails_optin_verify',
							'rpt_optin_verify',
						),
						'ajax_prefix'       => 'rpt',
						'product_name'      => 'Related Posts Thumbnails',
					),
					'telemetry'       => array(
						'optout_submit_key' => 'rpt-submit-optout',
					),
					'menu'            => array(
						'slug'    => 'related-posts-thumbnails',
						'account' => false,
						'support' => false,
					),
					'settings'        => array(
						'_rpt_optin'                                          => '',
						'wpb_sdk_related-posts-thumbnails'                    => '',
						'wpb_sdk_related-posts-thumbnails_fallback_verify_token' => '',
						'wpb_sdk_related-posts-thumbnails_initial_log_sent'   => '',
						'rpt_settings_schema'                                 => false,
						'rpt_settings_general'                                => false,
						'rpt_settings_thumbnails'                             => false,
						'rpt_settings_styles'                                 => false,
						'rpt_settings_relations'                              => false,
						'rpt_active_time'                                     => false,
					),
				)
			);
		}

		return $rpt_wpb92640233;
	}

	rpt_wpb92640233();
	do_action( 'rpt_wpb92640233_loaded' );
}

if ( ! function_exists( 'rpt_response_is_image_content_type' ) ) {
	/**
	 * Whether an HTTP response reports an image content type.
	 *
	 * @param array $response Remote response.
	 * @return bool
	 * @since 5.0.0
	 */
	function rpt_response_is_image_content_type( $response ) {
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );

		if ( is_array( $content_type ) ) {
			$content_type = reset( $content_type );
		}

		$content_type = strtolower( trim( (string) $content_type ) );

		if ( false !== strpos( $content_type, ';' ) ) {
			$content_type = trim( strstr( $content_type, ';', true ) );
		}

		return 0 === strpos( $content_type, 'image/' );
	}
}

if ( ! function_exists( 'rpt_remote_image_url_is_accessible' ) ) {
	/**
	 * Whether a remote image URL responds with a reachable image.
	 *
	 * @param string $url Sanitized image URL.
	 * @return bool
	 * @since 5.0.0
	 */
	function rpt_remote_image_url_is_accessible( $url ) {
		$request_args = array(
			'timeout'     => 4,
			'redirection' => 5,
		);

		$response = wp_remote_head( $url, $request_args );

		if ( ! is_wp_error( $response ) ) {
			$code = (int) wp_remote_retrieve_response_code( $response );

			if ( $code >= 200 && $code < 400 && rpt_remote_image_response_is_valid( $url, $response ) ) {
				return true;
			}
		}

		$response = wp_remote_get( $url, $request_args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		return $code >= 200 && $code < 400 && rpt_remote_image_response_is_valid( $url, $response );
	}
}

if ( ! function_exists( 'rpt_remote_image_response_is_valid' ) ) {
	/**
	 * Whether a successful HTTP response looks like an image.
	 *
	 * @param string $url      Image URL.
	 * @param array  $response Remote response.
	 * @return bool
	 * @since 5.0.0
	 */
	function rpt_remote_image_response_is_valid( $url, $response ) {
		if ( rpt_response_is_image_content_type( $response ) ) {
			return true;
		}

		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		$ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp', 'ico' ), true );
	}
}

if ( ! function_exists( 'rpt_is_valid_remote_image_url' ) ) {
	/**
	 * Whether a URL points to an accessible remote image.
	 *
	 * @param string $url Image URL.
	 * @return bool
	 * @since 5.0.0
	 */
	function rpt_is_valid_remote_image_url( $url ) {
		$url = trim( (string) $url );

		if ( '' === $url ) {
			return false;
		}

		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );

		if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
			return false;
		}

		$sanitized_url = esc_url_raw( $url );

		if ( empty( $sanitized_url ) || ! filter_var( $sanitized_url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		if ( function_exists( 'wp_http_validate_url' ) && ! wp_http_validate_url( $sanitized_url ) ) {
			return false;
		}

		// Avoid re-issuing blocking HTTP requests on every settings save for a URL
		// that was already checked; the REST settings-save request only pays the
		// network round trip once per URL per cache window.
		$cache_key = 'rpt_remote_img_' . md5( $sanitized_url );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return '1' === $cached;
		}

		$is_accessible = rpt_remote_image_url_is_accessible( $sanitized_url );

		set_transient( $cache_key, $is_accessible ? '1' : '0', 5 * MINUTE_IN_SECONDS );

		return $is_accessible;
	}
}


require_once plugin_dir_path( __FILE__ ) . 'inc/rpt-cache.php'; // NOSONAR — WordPress plugin bootstrap (php:S4833)
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-related-query.php'; // NOSONAR — WordPress plugin bootstrap (php:S4833)
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-related-renderer.php'; // NOSONAR — WordPress plugin bootstrap (php:S4833)

class RelatedPostsThumbnails // NOSONAR — WPCS Class_Name + public API surface (php:S101, php:S1448)
{
    /* Default values.
     * PHP 8.0 compatible
     * */
    public $single_only = '1';
    public $auto = '1';
    public $author_related = '0';
    public $top_text = '';
    public $number = 3;
    public $relation = 'categories';
    public $poststhname = 'thumbnail';
    public $background = '#ffffff';
    public $hoverbackground = '#eeeeee';
    public $border_color = '#dddddd';
    public $font_color = '#333333';
    public $font_family = 'Arial';
    public $font_size = '12';
    public $text_length = '100';
    public $excerpt_length = '0';
    public $custom_field = '';
    public $customfield_type = 'image';
    public $custom_height = '100';
    public $custom_width = '100';
    public $text_block_height = '75';
    public $thsource = 'post-thumbnails';
    public $categories_all = '1';
    public $devmode = '0';
    public $format = 'j F, Y';
    public $output_style = 'div';
    public $post_types = array('post');
    public $custom_taxonomies = array();
    public $default_image = '';
    public $default_image_type = 'image';
    public $relpoststh_column = '3';
    public $relpoststh_column_t = '2';
    public $relpoststh_column_m = '2';
    public $relpoststh_image_size = '1/1';
    public $wp_version = '';

    protected $wp_kses_rp_args = array('h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(), 'strong' => array());
    protected static $instance = null;

    /**
     * Function Constructor
     */
    public function __construct()
    {

        $this->constant();
        $this->default_image = esc_url(plugins_url('img/default.png', __FILE__));

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        add_action( 'init', array( $this, 'rpt_register_content_hooks' ) );

        add_action('admin_menu', array($this, 'admin_menu'));

        $this->wp_version = get_bloginfo('version');

        add_action('admin_init', array($this, 'redirect_optin'));
        add_action('admin_init', array($this, 'review_notice'));
		add_action('amp_post_template_css',array( $this, 'rpt_ampforwp_add_custom_css'));

        add_shortcode('related-posts-thumbnails', array($this, 'related_posts_shortcode'));

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'filter_plugin_action_links'));
        add_action('init', array($this, 'textdomain'), 0);

        add_action( 'save_post', array( $this, 'rpt_invalidate_post_cache_on_save' ) );
        add_action( 'wp_trash_post', array( $this, 'rpt_invalidate_post_cache_on_delete' ) );
        add_action( 'before_delete_post', array( $this, 'rpt_invalidate_post_cache_on_delete' ) );
        add_action( 'admin_init', 'rpt_batch_delete_cache_meta' );
        add_action( 'admin_bar_menu', array( $this, 'rpt_admin_bar_cache_purge' ), 100 );
        add_action( 'admin_post_rpt_purge_cache', array( $this, 'rpt_handle_admin_purge_cache' ) );
        add_action( 'admin_notices', array( $this, 'rpt_cache_purge_admin_notice' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'rpt_admin_bar_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'rpt_admin_bar_styles' ) );

        add_action( 'user_register', 'rpt_flush_multiple_publishers_cache' );
        add_action( 'deleted_user', 'rpt_flush_multiple_publishers_cache' );
        add_action( 'set_user_role', 'rpt_flush_multiple_publishers_cache' );
        add_action( 'add_user_role', 'rpt_flush_multiple_publishers_cache' );
        add_action( 'remove_user_role', 'rpt_flush_multiple_publishers_cache' );

    }

    /**
     * Load Languages
     *
     * @since 4.1.1
     * @version 5.0.0
     */
    public function textdomain() {

    // Explicitly load .mo from plugin's locale/ folder (avoids path/cache issues on Windows).
    $locale = determine_locale();
    $mofile = plugin_dir_path( __FILE__ ) . 'locale/related-posts-thumbnails-' . $locale . '.mo';
    if ( file_exists( $mofile ) ) {
        load_textdomain( 'related-posts-thumbnails', $mofile, $locale );
    }
    $this->top_text = '<h3>' . esc_html__( 'Related posts:', 'related-posts-thumbnails' ) . '</h3>';
  }

    /**
     * Show Opt-in Page.
     */
    public function render_optin()
    {
        if ( function_exists( 'wpb_sdk_render_optin_form' ) ) {
            wpb_sdk_render_optin_form( 'related-posts-thumbnails' );
        }
    }

    /**
     * Redirect first-time admins to the opt-in screen; skip opt-in page when already opted in.
     *
     * @return void
     */
    public function redirect_optin()
    {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['page'] ) ) : '';
        $decision = function_exists( 'wpb_sdk_get_optin_decision' )
            ? wpb_sdk_get_optin_decision( 'related-posts-thumbnails' )
            : (string) get_option( '_rpt_optin', '' );

        if (
            $page
            && in_array( $page, array( 'related-posts-thumbnails', 'rpt' ), true )
            && '' === $decision
        ) {
            $page_redirect = 'rpt' === $page ? 'rpt' : 'related-posts-thumbnails';
            wp_safe_redirect(
                admin_url( 'admin.php?page=rpt-optin&redirect-page=' . rawurlencode( $page_redirect ) )
            );
            exit;
        }

        if (
            (
                function_exists( 'wpb_sdk_should_redirect_from_optin_page' )
                    ? wpb_sdk_should_redirect_from_optin_page( 'related-posts-thumbnails' )
                    : ( 'yes' === $decision )
            )
            && 'rpt-optin' === $page
        ) {
            wp_safe_redirect( admin_url( RELATED_POSTS_THUMBNAILS_ADMIN_URL_PATH ) );
            exit;
        }
    }

    /**
     * Action link on plugin page
     *
     * @param array $actions
     * @return array
     *
     * @access public
     * @return array
     * @since 2.1.4
     * @version 6.0.0
     */
    public function filter_plugin_action_links($actions_links)
    {
        $settings_link = '<a href="' . esc_url(admin_url(RELATED_POSTS_THUMBNAILS_ADMIN_URL_PATH)) . '">' . esc_html__('Settings', 'related-posts-thumbnails') . '</a>';
        array_unshift($actions_links, $settings_link);

        return $actions_links;
    }


    /**
     * Prevent related posts on editor screen (builders).
     *
     * @return bool $return
     */
    public function prevent_on_editors()
    {
        $return = false;
        $prevent_on_edit = apply_filters('rpt_prevent_on_edit', array('divi' => false,));

        foreach ( $prevent_on_edit as $key => $value ) {
            if ( 'divi' === $key && $value && isset( $_GET['et_fb'] ) ) {
                $et_fb = sanitize_text_field( wp_unslash( (string) $_GET['et_fb'] ) );

                if ( '' !== $et_fb ) {
                    $return = true;
                }
            }
        }

        return $return;
    }

    /**
     * callback for shortcode related_posts_shortcode
     *
     * @param array $atts attributes of shortcode
     * @version 6.0.0
     */
    public function related_posts_shortcode($atts)
    {

        $atts = shortcode_atts(array(
            'posts_number' => '3',
            'posts_sort' => 'random',
            'main_title' => '',
            'exclude_post' => ''),
            $atts, 'related-posts-thumbnails'
        );

        $number = $atts['posts_number'];
        $sort   = RPT_RELATED_SORT_RAND;

        if ( 'latest' === $atts['posts_sort'] ) {
            $sort = 'post_date';
        }

        //sanitization through regex expression to know if a string is consisting of numeric values.
        $regex = '/^\d+(?:,\d+)*$/';
        $excluded_posts_array = preg_match($regex, $atts['exclude_post']) ? $atts['exclude_post'] : array();

        if (!is_numeric($number)) {
            $number = 3;
        }

        $main_title = str_replace('_', ' ', $atts['main_title']);

        if ( $this->rpt_should_skip_related_output() ) {
            return '';
        }

        return $this->get_thumbnails(true, $number, $sort, $main_title, $excluded_posts_array);

    }

    /**
     * Function to enqueue admin styles and scripts.
     *
     * @param $page
     * @return void
     * @since 1.7.0
     * @version 6.0.0
     *
     */
    public function admin_scripts($page)
    {
        if ('toplevel_page_related-posts-thumbnails' !== $page) {
            return;
        }

        wp_enqueue_media();

        $asset_file = plugin_dir_path(__FILE__) . 'build/index.asset.php';
        $script_url = plugins_url('build/index.js', __FILE__);
        $style_url  = plugins_url('build/index.css', __FILE__);

        $dependencies = array('wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components');
        $version      = RELATED_POSTS_THUMBNAILS_VERSION;

        if (file_exists($asset_file)) {
            $asset        = include $asset_file;
            $dependencies = $asset['dependencies'];
            $version      = $asset['version'];
        }

        wp_enqueue_style(
            'rpt-admin-settings',
            $style_url,
            array( 'wp-components' ),
            $version
        );

        wp_enqueue_script(
            'rpt-admin-settings',
            $script_url,
            $dependencies,
            $version,
            true
        );

        wp_localize_script(
            'rpt-admin-settings',
            'rptAdmin',
            array(
                'restUrl'   => rest_url('rpt/v1/'),
                'nonce'     => wp_create_nonce('wp_rest'),
                'assetsUrl' => plugins_url('assets/', __FILE__),
                'logoUrl'   => plugins_url('assets/images/rpt-brand.svg', __FILE__),
                'supportUrl' => 'https://wpbrigade.com/contact',
            )
        );

        wp_set_script_translations( 'rpt-admin-settings', 'related-posts-thumbnails', plugin_dir_path( __FILE__ ) . 'locale' );
    }

	/**
	 * Function to add custom CSS for AMP pages.
	 *
	 * This function outputs custom CSS specific to AMP (Accelerated Mobile Pages).
	 * It is intended to be used in a WordPress theme or plugin for customizing the styles of AMP pages.
	 *
	 * @return void
	 * @since 4.2.0
     * @version 6.0.0
	 */
	public function rpt_ampforwp_add_custom_css() { 
        include_once RELATED_POSTS_THUMBNAILS_PLUGIN_DIR . '/inc/amp.php';
     }
    /**
     * Function to define plugin Constants
     *
     * @return void
     * @version 6.0.0
     */
    public function constant()
    {
        define( 'RELATED_POSTS_THUMBNAILS_VERSION', '6.0.0' );
        define( 'RELATED_POSTS_THUMBNAILS_FEEDBACK_SERVER', 'https://wpbrigade.com/' );
        define( 'RELATED_POSTS_THUMBNAILS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'RELATED_POSTS_THUMBNAILS_ADMIN_URL_PATH', 'admin.php?page=related-posts-thumbnails' );
    }

    /**
     * Check either to show notice or not.
     *
     * @since 1.8.2
     */
    public function review_notice()
    {
        $this->review_dismissal();
        $this->review_prending();

        $review_dismissal = get_option('rpt_review_dismiss');

        if ('yes' == $review_dismissal) {
            return;
        }

        $activation_time = get_option('rpt_active_time');

        if (!$activation_time) {
            $activation_time = time();
            add_option('rpt_active_time', $activation_time);
        }

        // 1296000 = 15 Days in seconds.
        if (time() - $activation_time > 1296000) {
            add_action('admin_notices', array($this, 'review_notice_message'));
        }
    }

    /**
     * Show review Message After 15 days.
     *
     * @since 1.8.2
     */
    public function review_notice_message()
    {
		$request_uri = isset( $_SERVER['REQUEST_URI'] )
			? esc_url_raw( wp_unslash( (string) $_SERVER['REQUEST_URI'] ) )
			: '';
		$scheme      = ( wp_parse_url( $request_uri, PHP_URL_QUERY ) ) ? '&' : '?';
		$url         = $request_uri . $scheme . 'rpt_review_dismiss=yes';
		$dismiss_url = wp_nonce_url( $url, 'rpt-review-nonce' );

		$_later_link = $request_uri . $scheme . 'ssb_review_later=yes';
		$later_url   = wp_nonce_url( $_later_link, 'rpt-review-nonce' );
		?>

        <style media="screen">
            .rpt-review-notice {
                padding: 15px 0;
                background-color: #fff;
                border-radius: 3px;
                margin: 20px 0px 10px 20px;
				margin-inline-end: 20px;
                border-inline-start: 4px solid transparent;
				display: flex;
            }

            .rpt-review-notice:after {
                content: '';
                display: table;
                clear: both;
            }

            .rpt-review-thumbnail {
                line-height: 80px;
                text-align: center;
                width: 117px;
            }

            .rpt-review-thumbnail img {
                height: 72px;
                vertical-align: middle;
                width: 72px;
            }

            .rpt-review-text {
                overflow: hidden;
            }

            .rpt-review-text h3 {
                font-size: 24px;
                margin: 0 0 5px;
                font-weight: 400;
                line-height: 1.3;
            }

            .rpt-review-text p {
                font-size: 13px;
                margin: 0 0 5px;
            }

            .rpt-review-ul {
                margin: 0;
                padding: 0;
            }

            .rpt-review-ul li {
                display: inline-block;
                margin-right: 15px;
            }

            .rpt-review-ul li a {
                display: inline-block;
                color: #10738B;
                text-decoration: none;
                padding-left: 26px;
                position: relative;
            }

            .rpt-review-ul li a span {
                position: absolute;
                left: 0;
                top: -2px;
            }
			@media (max-width: 767px){
				.rpt-review-thumbnail img{
					width: 40px;
					height: 40px;
				}
			}
        </style>

        <div class="rpt-review-notice">
            <div class="rpt-review-thumbnail">
                <img src="<?php echo esc_url( plugins_url( 'img/rpt-logo.svg', __FILE__ ) ); ?>" alt="<?php esc_attr_e( 'Related Posts Thumbnails', 'related-posts-thumbnails' ); ?>">
            </div>
            <div class="rpt-review-text">

                <h3>
                    <?php esc_html_e('Leave A Review?', 'related-posts-thumbnails'); ?>
                </h3>

                <p>
                    <?php esc_html_e('We hope you\'ve enjoyed using Related Post Thumbnails! Would you consider leaving us a review on WordPress.org?', 'related-posts-thumbnails'); ?>
                </p>

                <ul class="rpt-review-ul">
                    <li>
                        <a href="https://wordpress.org/support/plugin/related-posts-thumbnails/reviews/"
                           target="_blank"><span class="dashicons dashicons-external"></span>
                            <?php esc_html_e('Sure! I\'d love to!', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo esc_url( $dismiss_url ); ?>">
                            <span class="dashicons dashicons-smiley"></span>
                            <?php esc_html_e('I\'ve already left a review', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo esc_url( $later_url ); ?>">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php esc_html_e('Maybe Later', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo esc_url( $dismiss_url ); ?>">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php esc_html_e('Never show again', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <?php
    }

    /**
     * Set time to current so review notice will popup after 15 days
     *
     * @since 1.8.2
     * @version 6.0.0
     */
    public function review_prending()
    {
        if (!is_admin() || !current_user_can('manage_options') || !isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'rpt-review-nonce') || !isset($_GET['ssb_review_later'])) {

            return;
        }

        // Reset Time to current time.
        update_option('rpt_active_time', time());
    }

    /**
     * Check and Dismiss review message.
     *
     * @since 1.8.2
     * @version 6.0.0
     */
    private function review_dismissal()
    {
        if (!is_admin() || !current_user_can('manage_options') || !isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'rpt-review-nonce') || !isset($_GET['rpt_review_dismiss'])) {

            return;
        }

        add_option('rpt_review_dismiss', 'yes');
    }

    /**
     * Check whether the stored default image path uses a legacy plugin URL.
     *
     * @return bool True when the path is an old bundled default image URL.
     * @since  6.0.0
     */
    private function rpt_is_old_default_img() {
        $stored = (string) rpt_settings()->rpt_get( 'general.default_image', '' );

        if ( $stored !== $this->default_image ) {
            $chunks = explode( '/', $stored );
            if ( in_array( 'related-posts-thumbnails', $chunks, true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Upgrade old plugin-bundled default image path in tab settings.
     *
     * @return void
     * @since  6.0.0
     */
    public function rpt_maybe_fix_default_image_path() {
        if ( rpt_settings()->rpt_needs_migration_consent() ) {
            return;
        }

        if ( ! $this->rpt_is_old_default_img() ) {
            return;
        }

        $general = rpt_settings()->rpt_get( 'general', array() );
        $general['default_image'] = $this->default_image;
        rpt_settings()->rpt_save_tab( 'general', $general );
    }

    /**
     * Get the configured default thumbnail URL based on the selected type.
     *
     * @return string Default image URL.
     */
    public function rpt_get_default_image_url() {
        $type = (string) rpt_settings()->rpt_get( 'general.default_image_type', $this->default_image_type );

        if ( 'url' === $type ) {
            $remote_url = (string) rpt_settings()->rpt_get( 'general.default_image_url', '' );

            if ( ! empty( $remote_url ) ) {
                return esc_url( $remote_url );
            }
        }

        $stored = (string) rpt_settings()->rpt_get( 'general.default_image', $this->default_image );

        return '' !== $stored ? $stored : $this->default_image;
    }

    /**
     * Automatically displaying related posts under post body
     *
     * @param $content
     *
     * @return void
     * @version 6.0.0
     */
    public function auto_show($content)
    {
        if ( $this->rpt_should_skip_related_output() ) {
            return $content;
        }

        return $this->rpt_append_content_html( $content, $this->get_html( true ) );
    }

    /**
     * Whether related output should be skipped for the current request.
     *
     * @return bool
     * @since 5.0.0
     */
    private function rpt_should_skip_related_output() {
        if ( $this->prevent_on_editors() ) {
            return true;
        }

        if ( rpt_is_mobile_hidden() ) {
            return true;
        }

        return false;
    }

    /**
     * Append related posts HTML to post content when markup exists.
     *
     * @param string $content Post content.
     * @param string $html    Related posts HTML.
     * @return string
     * @since 5.0.0
     */
    private function rpt_append_content_html( $content, $html ) {
        if ( empty( $html ) ) {
            return $content;
        }

        return $content . $html;
    }

    /**
     * Register automatic and author-related content filters after WordPress is ready.
     *
     * @return void
     * @since 5.0.0
     */
    public function rpt_register_content_hooks() {
        if ( $this->prevent_on_editors() ) {
            return;
        }

        $priority = (int) apply_filters( 'rpt_content_prioirty', 10 );

        if ( rpt_is_yes_option( 'relpoststh_auto', $this->auto ) && ! $this->rpt_is_author_related_enabled() ) {
            add_filter( 'the_content', array( $this, 'auto_show' ), $priority );
            add_filter( 'render_block', array( $this, 'rpt_append_to_post_content_block' ), $priority, 2 );
        }

        if ( $this->rpt_is_author_related_enabled() ) {
            add_filter( 'the_content', array( $this, 'rpt_author_related_show' ), $priority );
            add_filter( 'render_block', array( $this, 'rpt_append_author_related_to_post_content_block' ), $priority, 2 );
        }
    }

    /**
     * Append related posts after the post content block (block theme fallback).
     *
     * @param string $block_content Rendered block HTML.
     * @param array  $block         Block data.
     * @return string
     * @since 5.0.1
     */
    public function rpt_append_to_post_content_block( $block_content, $block ) {
        if ( $this->rpt_should_skip_related_output() ) {
            return $block_content;
        }

        if ( ! is_array( $block ) || empty( $block['blockName'] ) || 'core/post-content' !== $block['blockName'] ) {
            return $block_content;
        }

        if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
            return $block_content;
        }

        if ( false !== strpos( $block_content, 'relpost-thumb-wrapper' ) ) {
            return $block_content;
        }

        return $this->rpt_append_content_html( $block_content, $this->get_html( true ) );
    }

    /**
     * Append author-related posts after the post content block (block theme fallback).
     *
     * @param string $block_content Rendered block HTML.
     * @param array  $block         Block data.
     * @return string
     * @since 5.0.1
     */
    public function rpt_append_author_related_to_post_content_block( $block_content, $block ) {
        if ( ! is_array( $block ) || empty( $block['blockName'] ) || 'core/post-content' !== $block['blockName'] ) {
            return $block_content;
        }

        if ( ! is_singular() || ! in_the_loop() || ! is_main_query() || ! $this->rpt_should_show_author_related() ) {
            return $block_content;
        }

        if ( false !== strpos( $block_content, 'relpost-author-related' ) ) {
            return $block_content;
        }

        return $this->rpt_append_content_html( $block_content, $this->rpt_get_author_related_html() );
    }

    /**
     * Whether author-related auto display is the active mode.
     *
     * @return bool
     * @since 5.0.0
     */
    public function rpt_is_author_related_enabled() {
        return rpt_is_yes_option( 'relpoststh_author_related', $this->author_related ) && $this->rpt_has_multiple_publishers();
    }

    /**
     * Whether the site has more than one user who can publish posts.
     *
     * @return bool
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_has_multiple_publishers() {
        if ( ! did_action( 'init' ) ) {
            return false;
        }

        static $request_cache = null;

        if ( null !== $request_cache ) {
            return $request_cache; // NOSONAR — request-scoped cache (php:S1488)
        }

        $stored = get_transient( RPT_MULTIPLE_PUBLISHERS_TRANSIENT );

        if ( false !== $stored ) {
            $request_cache = (bool) $stored; // NOSONAR — request-scoped cache (php:S1488)

            return $request_cache; // NOSONAR — request-scoped cache (php:S1488)
        }

        $user_query = new WP_User_Query(
            array(
                'capability'  => 'publish_posts',
                'fields'      => 'ID',
                'number'      => 2,
                'count_total' => false,
            )
        );

        $request_cache = count( $user_query->get_results() ) > 1;

        set_transient( RPT_MULTIPLE_PUBLISHERS_TRANSIENT, $request_cache ? 1 : 0, DAY_IN_SECONDS );

        return $request_cache; // NOSONAR — also written to transient before return (php:S1488)
    }

    /**
     * Append author-related posts to post content, same placement as auto-append.
     *
     * @param string $content Post content.
     * @return string
     * @since 5.0.0
     */
    public function rpt_author_related_show( $content ) {
        if ( ! $this->rpt_should_show_author_related() ) {
            return $content;
        }

        return $this->rpt_append_content_html( $content, $this->rpt_get_author_related_html() );
    }

    /**
     * Whether author-related posts should output on the current request.
     *
     * @return bool
     * @since 5.0.0
     */
    public function rpt_should_show_author_related() {
        if ( ! $this->rpt_is_author_related_enabled() ) {
            return false;
        }

        if ( $this->prevent_on_editors() ) {
            return false;
        }

        if ( rpt_is_mobile_hidden() ) {
            return false;
        }

        return $this->is_relpoststh_show();
    }

    /**
     * Build related posts markup limited to the current post author.
     *
     * @return string
     * @since 5.0.0
     */
    public function rpt_get_author_related_html() {
        $post_id = get_the_ID();

        if ( ! $post_id ) {
            return '';
        }

        $author_id = (int) get_post_field( 'post_author', $post_id );
        $author_id = (int) apply_filters( 'rpt_author_related_author_id', $author_id, $post_id );

        if ( $author_id <= 0 ) {
            return '';
        }

        $html = $this->get_thumbnails( true, '', '', '', '', $author_id );

        if ( empty( $html ) ) {
            return '';
        }

        return '<div class="relpost-author-related">' . $html . '</div>';
    }

    /**
     * Getting related posts HTML
     *
     * @param boolean $show_top
     * @return string
     * @version 6.0.0
     */
    public function get_html($show_top = false)
    {
        if ( $this->rpt_should_skip_related_output() ) {
            return '';
        }

        if ($this->is_relpoststh_show()) {
            return $this->get_thumbnails($show_top);
        }

        return '';
    }

    /**
     * Query related post IDs for a source post.
     *
     * @param int                  $post_id    Source post ID.
     * @param array<string, mixed> $query_args Query arguments.
     * @param string               $debug      Debug output passed by reference.
     * @return array<int>
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_query_related_post_ids( $post_id, $query_args, &$debug = '' ) {
        $query = new RPT_Related_Query( $this );

        return $query->rpt_query_related_post_ids( $post_id, $query_args, $debug );
    }

    /**
     * Function responsible for Thumbnail creation.
     *
     * @param boolean $show_top Position of the thumbnails.
     * @param string $posts_number Number of posts to display.
     * @param string $sort_by sort The thumbnails by some filter.
     * @param string $main_title Thumbnail title.
     * @param string $exclude post_ids To exclude from related posts thumbnails.
     * @param int    $author_id Limit results to this post author when greater than zero.
     *
     * @return string
     * @version 6.0.0
     *
     * @since 1.0.0
     */
    public function get_thumbnails($show_top = false, $posts_number = '', $sort_by = '', $main_title = '', $exclude = '', $author_id = 0)
    {
        $renderer = new RPT_Related_Renderer( $this );

        return $renderer->rpt_render( $show_top, $posts_number, $sort_by, $main_title, $exclude, $author_id );
    }

    /**
     * Show Related Post Categories.
     *
     * @param int $id The ID of post.
     *
     * @return string $category_list The structure of category list.
     *
     * @since 2.2.0
     * @version 6.0.0
     */
    public function relpoststh_category_list($id, $taxonomy, $post_type)
    {

        $category_list_struct = '';

        $category_list = $this->relpoststh_get_featured_category($id, $taxonomy, $post_type);

        // Bail early if category list is not found.
        if (false === $category_list) {
            return $category_list_struct;
        }

        /**
         * Modifiy the category listings.
         *
         * @param int $id The ID of post.
         * @return string $category_list The category list of current post at hand.
         * @return string $taxonomy The taxonomy of a post.
         *
         * @since 2.2.0
		 * @version 4.3.0
         */
        $category_list = apply_filters('relpoststh_show_all_categories', $category_list, $id, $taxonomy, $post_type);
        $category_names = array();

		if ( is_array( $category_list ) && ! empty( $category_list[0] ) ) {
			foreach ( $category_list[0] as $item ) {
				if ( isset( $item->name ) ) {
					$category_names[] = $item->name;
				}
			}
		} elseif ( ! empty( $category_list->name ) ) {
			$category_names[] = $category_list->name;
		}

		$html = '';

        if (!empty($category_names)) {
            $category_names = implode(', ', $category_names);
            $html .= '<div class="relpoststh_front_cat">';
            $html .= '<span>' . esc_html($category_names) . '</span>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Returns the featured category of a post based on YOAST SEO's meta-data.
     *
     * @param int $post_ID The post id.
     * @param int $taxonomy_name The taxonomy name.
     *
     * @return object
     * @since 2.2.0
     * @version 6.0.0
     */
    public function relpoststh_get_featured_category($post_ID, $taxonomy_name = 'category', $post_type = 'post')
    {

        // if the ID is not set, get the global ID.
        if (!is_numeric($post_ID)) {
            $post_ID = get_the_ID();
        }

        $args = array(
            'post_type' => $post_type,
        );

        $taxonomy_name = is_array($taxonomy_name) ? $taxonomy_name[0] : $taxonomy_name;
        $categories = wp_get_object_terms($post_ID, $taxonomy_name, $args);

        if (!empty($categories) && is_wp_error($categories)) {
            return false;
        }

        // get the yoast 'primary_category' from meta-data.
        $yoast_primary_category = get_post_meta($post_ID, '_yoast_wpseo_primary_' . $taxonomy_name, true);

        if ($yoast_primary_category) {

            // if meta-data exists, find the primary category.
            foreach ($categories as $category) {

                if ($yoast_primary_category == $category->term_id) {
                    return $category;
                }
            }
        }

        // return the first category.
        if (isset($categories) && !empty($categories[0])) {
            return $categories[0];
        }
    }

    /**
     * This will add debugging information in HTML source
     *
     * @param $output
     * @param  $debug
     * @param int $time time took to create the thumbnails
     *
     * @return $output debugged information
     * @version 6.0.0
     */
    public function finish_process($output, $debug, $time)
    {

        $devmode = rpt_settings_bool( 'general.dev_mode' );

        if ($devmode) {
            $time = microtime(true) - $time;
            $debug .= "Plugin execution time: $time sec;";
            $output .= '<!-- ' . esc_html( $debug ) . ' -->';
        }

        return $output;
    }

    /**
     * Process text cut.
     *
     * @param string $text Text to cut.
     * @param int $length Length to cut.
     * @return string
     * @version 6.0.0
     */
    public function process_text_cut($text, $length)
    {
        if ( 0 == $length ) {
            return '';
        }

        $text = wp_strip_all_tags( strip_shortcodes( $text ) );

        if ( function_exists( 'mb_strlen' ) ) {
            if ( mb_strlen( $text ) > $length ) {
                return mb_substr( $text, 0, $length ) . '...';
            }

            return $text;
        }

        if ( strlen( $text ) > $length ) {
            return substr( $text, 0, $length ) . '...';
        }

        return $text;
    }

    /**
     * Function to check th options to show the thumbnails according to the post types, categories etc.
     *
     * @return boolean
     * @version 6.0.0
     */
    public function is_relpoststh_show()
    {
        // Checking display options
        if ( ! is_single() && rpt_settings_bool( 'general.single_only', true ) ) { // single only
            return false;
        }
        // Check post type
        $post_types = (array) rpt_settings()->rpt_get( 'general.post_types', $this->post_types );
        $post_type = get_post_type();

        if (!is_array($post_types) || !in_array($post_type, $post_types, true)) {
            return false;
        }
        // Check categories
        $id = get_the_ID();
        $query_scope = rpt_settings()->rpt_get( 'general.query_scope', array( 'mode' => 'all', 'category_ids' => array() ) );
        $categories_all = rpt_settings_scope_is_all( $query_scope ) ? '1' : '0';

        if ( '1' !== $categories_all ) { // only specific categories were selected

            $post_categories = wp_get_object_terms($id, array('category'), array('fields' => 'ids'));
            $relpoststh_categories = rpt_settings_scope_ids( $query_scope );

            if (!is_array($relpoststh_categories) || !is_array($post_categories)) { // no categories were selcted or post doesn't belong to any
                return false;
            }

            $common_categories = array_intersect($relpoststh_categories, $post_categories);

            if (empty($common_categories)) { // post doesn't belong to specified categories
                return false;
            }
        }

        return true;
    }

    /**
     * Clear related ID cache when a post is saved.
     *
     * @param int $post_id Post ID.
     * @return void
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_invalidate_post_cache_on_save( $post_id ) {
        if ( ! rpt_is_related_cache_enabled() ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
            return;
        }

        rpt_delete_post_related_cache( $post_id );
    }

    /**
     * Clear related ID cache when a post is trashed or deleted.
     *
     * @param int $post_id Post ID.
     * @return void
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_invalidate_post_cache_on_delete( $post_id ) {
        if ( ! rpt_is_related_cache_enabled() ) {
            return;
        }

        rpt_delete_post_related_cache( $post_id );
    }

    /**
     * Add purge cache link to the admin bar.
     *
     * @param WP_Admin_Bar $admin_bar Admin bar instance.
     * @return void
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_admin_bar_cache_purge( $admin_bar ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) || ! rpt_is_related_cache_enabled() ) {
            return;
        }

        $admin_bar->add_node(
            array(
                'id'    => 'rpt-purge-cache',
                'title' => sprintf(
                    '<span class="ab-icon" aria-hidden="true"></span><span class="ab-label">%s</span>',
                    esc_html__( 'Purge RPT Cache', 'related-posts-thumbnails' )
                ),
                'href'  => wp_nonce_url(
                    admin_url( 'admin-post.php?action=rpt_purge_cache' ),
                    'rpt_purge_cache'
                ),
                'meta'  => array(
                    'title' => esc_attr__( 'Purge RPT Cache', 'related-posts-thumbnails' ),
                ),
            )
        );
    }

    /**
     * Enqueue admin bar icon styles for the purge cache link.
     *
     * @return void
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_admin_bar_styles() {
        if ( ! is_admin_bar_showing() || ! is_user_logged_in() || ! current_user_can( 'manage_options' ) || ! rpt_is_related_cache_enabled() ) {
            return;
        }

        $icon_url = esc_url( plugin_dir_url( __FILE__ ) . 'img/rpt-menu-icon.svg' );
        $css      = '#wpadminbar #wp-admin-bar-rpt-purge-cache > .ab-item .ab-icon:before {
            background: url("' . $icon_url . '") center / contain no-repeat;
            content: "";
            display: block;
            float: left;
            height: 20px;
            margin: 3px 0 0;
            width: 20px;
        }';

        wp_enqueue_style( 'admin-bar', false, array(), RELATED_POSTS_THUMBNAILS_VERSION );
        wp_add_inline_style( 'admin-bar', $css );
    }

    /**
     * Handle admin bar cache purge requests.
     *
     * @return void
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_handle_admin_purge_cache() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No access', 'related-posts-thumbnails' ) );
        }

        check_admin_referer( 'rpt_purge_cache' );

        rpt_purge_all_related_cache();

        $redirect = wp_get_referer();

        if ( ! $redirect ) {
            $redirect = admin_url( RELATED_POSTS_THUMBNAILS_ADMIN_URL_PATH );
        }

        wp_safe_redirect( add_query_arg( 'rpt_cache_purged', '1', $redirect ) );
        exit;
    }

    /**
     * Show cache purge notices in admin.
     *
     * @return void
     * @since 5.0.0
     * @version 6.0.0
     */
    public function rpt_cache_purge_admin_notice() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['rpt_cache_purged'] ) && '1' === sanitize_text_field( wp_unslash( (string) $_GET['rpt_cache_purged'] ) ) ) {
            $pending = '1' === get_option( RPT_CACHE_PURGE_PENDING_OPTION, '0' );
            $message = $pending
                ? esc_html__( 'Related posts cache purge started. Cleanup will continue in the background.', 'related-posts-thumbnails' )
                : esc_html__( 'Related posts cache purged successfully.', 'related-posts-thumbnails' );

            echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
        }
    }

    /**
     * Admin Menu page
     *
     * @return void
     * @version 6.0.0
     */
    public function admin_menu() {
	add_menu_page(
		__( 'Related Posts Thumbnails', 'related-posts-thumbnails' ),
		__( 'Related Posts', 'related-posts-thumbnails' ),
		'manage_options',
		'related-posts-thumbnails',
		array( $this, 'admin_interface' ),
		plugin_dir_url( __FILE__ ) . 'img/rpt-menu-icon.svg'
	);

	add_submenu_page(
		'related-posts-thumbnails',
		__( 'Activate', 'related-posts-thumbnails' ),
		' ',
		'manage_options',
		'rpt-optin',
		array( $this, 'render_optin' )
	);

	add_action( 'admin_head', array( $this, 'admin_menu_css' ) );
}

    /**
     * Admin menu CSS.
     *
     * @return void
     * @version 6.0.0
     */
public function admin_menu_css() {
	?>
	<style>
		/* Related Posts menu icon */
		#adminmenu .toplevel_page_related-posts-thumbnails img {
			width: 20px;
			height: 20px;
			padding: 7px 0;
			opacity: 1;
			transition: opacity .2s ease;
		}

		#adminmenu .toplevel_page_related-posts-thumbnails:hover img,
		#adminmenu .toplevel_page_related-posts-thumbnails.wp-has-current-submenu img,
		#adminmenu .toplevel_page_related-posts-thumbnails.current img {
			opacity: 1;
		}
	</style>
	<?php
}

	/**
	 * Retrieves the category list for a post based on provided conditions.
	 *
	 * @param bool   $show_category Whether to show categories.
	 * @param array  $taxonomies    Array of taxonomies.
	 * @param WP_Post|int $post     Post object or post ID.
	 * @param string $post_type     The type of the post.
	 *
	 * @since 4.3.0
	 * @return array|string Category list array or empty string.
	 */
	public function rpt_get_post_category_list( $show_category, $taxonomies, $post, $post_type ) {
		// Ensure $post is a valid post ID or object
		if ( is_object( $post ) && isset( $post->ID ) ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = intval( $post );
		} else {
			$post_id = 0;
		}

		if ( ! $show_category || $post_id === 0 ) {
			return '';
		}

		if ( is_array( $taxonomies ) && isset( $taxonomies[0] ) ) {
			return $this->relpoststh_category_list( $post_id, $taxonomies[0], $post_type );
		}
		
		// Add missing return statement
		return '';
	}

    /**
     * Related post thumbnail settings page load
     *
     * @return void
     * @version 6.0.0
     */
    public function admin_interface()
    {
        include_once RELATED_POSTS_THUMBNAILS_PLUGIN_DIR . '/inc/rpt-settings.php';
    }

    /**
     * Category List in Settings
     *
     * @param  $categoriesall
     * @param  $categories
     * @param  $selected_categories
     * @param  $all_name
     * @param  $specific_name
     * @version 6.0.0
     */
    public function display_categories_list($categoriesall, $categories, $selected_categories, $all_name, $specific_name)
    { ?>
        <input style="display:none" id="<?php echo esc_attr($all_name) . '_check'; ?>" class="select_all"
               type="checkbox" name="<?php echo esc_attr($all_name); ?>" value="1" <?php checked( $categoriesall, '1' ); ?>
        />
        <label style="display:none" for="<?php echo esc_attr($all_name); ?>">
            <?php esc_html_e('All', 'related-posts-thumbnails'); ?>
        </label>
        <div class="select_specific" <?php echo esc_attr($specific_name);
        if ($categoriesall == '1'): ?>
            style="display:none"
        <?php endif; ?> >
        </div>
        <select class="chosen-select <?php echo esc_attr($all_name); ?>"
                data-placeholder="<?php esc_attr_e('Select Categories', 'related-posts-thumbnails'); ?>"
                id="<?php echo esc_attr($all_name); ?>" name="<?php echo esc_attr($specific_name); ?>[]" multiple>
            <option value="0" <?php if ($categoriesall == '1') {
                echo 'selected="selected"';
            } ?>><?php esc_html_e('All', 'related-posts-thumbnails'); ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?php echo esc_attr($category->cat_ID); ?>" <?php
                if ($categoriesall !== '1' && in_array($category->cat_ID, (array)$selected_categories)) {
                    echo 'selected="selected"';
                }
                ?>><?php echo esc_html($category->cat_name); ?></option>
            <?php endforeach; ?>
        </select>
    <?php }

    /**
     * Main Instance
     *
     * @return Main instance
     * @see related_posts_thumbnails_loader()
     * @since 2.0.1
     * @static
     * @version 6.0.0
     */
    public static function instance()
    {

        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

/**
 * Returns the main instance of WP to prevent the need to use globals.
 *
 * @return RelatedPostsThumbnails instance
 * @since  2.0.1
 * @version 6.0.0
 */
if (!function_exists('related_posts_thumbnails_loader')) {
    function related_posts_thumbnails_loader()
    {
        return RelatedPostsThumbnails::instance();
    }
}

// Call the function.
global $related_posts_thumbnails;
$related_posts_thumbnails = related_posts_thumbnails_loader();

require_once plugin_dir_path( __FILE__ ) . 'inc/rpt-sanitize.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-settings-schema.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-settings-migration.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-settings-repository.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/rpt-settings-helpers.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-settings-service.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/class-rpt-rest-api.php';

RPT_Settings_Repository::rpt_boot( $related_posts_thumbnails );
$related_posts_thumbnails->rpt_maybe_fix_default_image_path();

$rpt_settings_service = new RPT_Settings_Service( $related_posts_thumbnails );
$rpt_rest_api         = new RPT_REST_API( $rpt_settings_service );
$rpt_rest_api->init();


// Include frontend assets handler.
include_once plugin_dir_path( __FILE__ ) . 'inc/rpt-assets.php';
RPT_Frontend_Assets::rpt_init();

// Include Widget File.
include_once plugin_dir_path(__FILE__) . 'inc/rpt-widget.php';
// Include Blocks File.
include_once plugin_dir_path(__FILE__) . 'inc/rpt-blocks.php';

?>
