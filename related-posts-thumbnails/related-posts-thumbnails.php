<?php
/**
 * Plugin Name:  Related Posts Thumbnails
 * Plugin URI:   https://wpbrigade.com/wordpress/plugins/related-posts/?utm_source=related-posts-lite&utm_medium=plugin-uri&utm_campaign=pro-upgrade-rp
 * Description:  Showing related posts thumbnails under the posts.
 * Version:      5.0.1
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
					'public_key'      => '1|4aOA8EuyIN4pi2miMvC23LLpnHbBZFNki9R9pVmwd673d3c8',
					'secret_key'      => 'sk_b36c525848fee035',
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
						'relpoststh_default_image'                            => false,
						'rpt_active_time'                                     => false,
						'relpoststh_single_only'                              => false,
						'relpoststh_mobile_view'                              => false,
						'relpoststh_post_types'                               => false,
						'relpoststh_onlywiththumbs'                           => false,
						'relpoststh_output_style'                             => false,
						'relpoststh_cleanhtml'                                => false,
						'relpoststh_column'                                   => false,
						'relpoststh_column_t'                                 => false,
						'relpoststh_column_m'                                 => false,
						'relpoststh_image_size'                               => false,
						'relpoststh_auto'                                     => false,
						'relpoststh_top_text'                                 => false,
						'relpoststh_number'                                   => false,
						'relpoststh_relation'                                 => false,
						'relpoststh_poststhname'                              => false,
						'relpoststh_background'                               => false,
						'relpoststh_hoverbackground'                          => false,
						'relpoststh_bordercolor'                              => false,
						'relpoststh_fontcolor'                                => false,
						'relpoststh_fontsize'                                 => false,
						'relpoststh_fontfamily'                               => false,
						'relpoststh_textlength'                               => false,
						'relpoststh_excerptlength'                            => false,
						'relpoststh_thsource'                                 => false,
						'relpoststh_customfield'                              => false,
						'relpoststh_theme_resize_url'                         => false,
						'relpoststh_customwidth'                              => false,
						'relpoststh_customheight'                             => false,
						'relpoststh_textblockheight'                          => false,
						'rpt_post_sort'                                       => false,
						'relpoststh_categories'                               => false,
						'relpoststh_categoriesall'                            => false,
						'relpoststh_show_categoriesall'                       => false,
						'relpoststh_show_categories'                          => false,
						'relpoststh_devmode'                                  => false,
						'relpoststh_startdate'                                => false,
						'relpoststh_custom_taxonomies'                        => false,
						'relpoststh_show_taxonomy'                            => false,
						'relpoststh_title_tag'                                => false,
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
			'timeout'     => 8,
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

		return rpt_remote_image_url_is_accessible( $sanitized_url );
	}
}

require_once plugin_dir_path( __FILE__ ) . 'inc/rpt-cache.php';

class RelatedPostsThumbnails
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
    public $column = '3';
    public $column_t = '2';
    public $column_m = '2';
    public $size = '1/1';
    public $wp_version = '';
	public $relpoststh_column = "3";
	public $relpoststh_column_t = "2";
	public $relpoststh_column_m = "2";
	public $relpoststh_image_size = "1/1";

    protected $wp_kses_rp_args = array('h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(), 'h5' => array(), 'h6' => array(), 'strong' => array());
    protected static $instance = null;

    /**
     * Function Constructor
     */
    function __construct()
    {

        $this->constant();
        $this->default_image = esc_url(plugins_url('img/default.png', __FILE__));
        $this->column = '3';
        $this->size = '1/1';

        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));

        // Compatibility for old default image path.
        if ($this->is_old_default_img())
            update_option('relpoststh_default_image', $this->default_image);

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
    function render_optin()
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
            wp_safe_redirect( admin_url( 'admin.php?page=related-posts-thumbnails' ) );
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
     */
    public function filter_plugin_action_links($actions_links)
    {
        $settings_link = '<a href="' . esc_url(admin_url('admin.php?page=related-posts-thumbnails')) . '">' . esc_html__('Settings', 'related-posts-thumbnails') . '</a>';
        array_unshift($actions_links, $settings_link);

        return $actions_links;
    }


    /**
     * Prevent related posts on editor screen (builders).
     *
     * @return bool $return
     */
    function prevent_on_editors()
    {
        $return = false;
        $prevent_on_edit = apply_filters('rpt_prevent_on_edit', array('divi' => false,));

        foreach ($prevent_on_edit as $key => $value) {
            switch ($key) {
                case 'divi':
                    if ($value && isset($_GET['et_fb'])) {
                        $return = true;
                    }
                    break;
            }
        }

        return $return;
    }

    /**
     * callback for shortcode related_posts_shortcode
     *
     * @param array $atts attributes of shortcode
     * @version 1.9.0
     */
    function related_posts_shortcode($atts)
    {

        $atts = shortcode_atts(array(
            'posts_number' => '3',
            'posts_sort' => 'random',
            'main_title' => '',
            'exclude_post' => ''),
            $atts, 'related-posts-thumbnails'
        );

        $number = $atts['posts_number'];

        if ($atts['posts_sort'] == 'random') {
            $sort = 'rand()';
        } elseif ($atts['posts_sort'] == 'latest') {
            $sort = 'post_date';
        }

        //sanitization through regex expression to know if a string is consisting of numeric values.
        $regex = '/^\d+(?:,\d+)*$/';
        $excluded_posts_array = preg_match($regex, $atts['exclude_post']) ? $atts['exclude_post'] : array();

        if (!is_numeric($number)) {
            $number = 3;
        }

        $main_title = str_replace('_', ' ', $atts['main_title']);

        return $this->get_thumbnails(true, $number, $sort, $main_title, $excluded_posts_array);

    }

    /**
     * Function to enqueue admin styles and scripts.
     *
     * @param $page
     * @return void
     * @since 1.7.0
     * @version 1.9.0
     *
     */
    function admin_scripts($page)
    {
        if ('toplevel_page_related-posts-thumbnails' === $page) {
            wp_enqueue_media();
            wp_enqueue_style('rpt_admin_css', plugins_url('assets/css/admin.css', __FILE__), false, RELATED_POSTS_THUMBNAILS_VERSION);
            wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.11.2/themes/smoothness/jquery-ui.css');
            // Enqueue Chosen CSS
            wp_enqueue_style('rpt-chosen', plugins_url('assets/css/chosen.min.css', __FILE__), array(), RELATED_POSTS_THUMBNAILS_VERSION);

            // Enqueue jQuery (if not already included by WordPress)
            if (!wp_script_is('jquery', 'enqueued')) {
                wp_enqueue_script('jquery');
            }

            // Enqueue Chosen JS
            wp_enqueue_script('rpt-chosen', plugins_url('assets/js/chosen.jquery.min.js', __FILE__), array('jquery'), RELATED_POSTS_THUMBNAILS_VERSION, true);

            // Enqueue other scripts
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker-alpha', plugins_url('assets/js/wp-color-picker-alpha.js', __FILE__), array('wp-color-picker'), RELATED_POSTS_THUMBNAILS_VERSION, true);

            wp_enqueue_script('rpt_admin_js', plugins_url('assets/js/admin.js', __FILE__),
                array(
                    'jquery',
                    'wp-color-picker',
                    'jquery-ui-datepicker',
                    'rpt-chosen'
                ), RELATED_POSTS_THUMBNAILS_VERSION
            );
        }
    }

	/**
	 * Function to add custom CSS for AMP pages.
	 *
	 * This function outputs custom CSS specific to AMP (Accelerated Mobile Pages).
	 * It is intended to be used in a WordPress theme or plugin for customizing the styles of AMP pages.
	 *
	 * @return void
	 * @since 4.2.0
	 */
	public function rpt_ampforwp_add_custom_css() { 
        // inlcude the file
        include_once RELATED_POSTS_THUMBNAILS_PLUGIN_DIR . '/inc/amp.php';
     }
    /**
     * Function to define plugin Constants
     *
     * @return void
     */
    function constant()
    {
        define('RELATED_POSTS_THUMBNAILS_VERSION', '5.0.1');
        define('RELATED_POSTS_THUMBNAILS_FEEDBACK_SERVER', 'https://wpbrigade.com/');
        define('RELATED_POSTS_THUMBNAILS_PLUGIN_DIR', plugin_dir_path(__FILE__));
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
        $scheme = (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
        $url = $_SERVER['REQUEST_URI'] . $scheme . 'rpt_review_dismiss=yes';
        $dismiss_url = wp_nonce_url($url, 'rpt-review-nonce');

        $_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'ssb_review_later=yes';
        $later_url = wp_nonce_url($_later_link, 'rpt-review-nonce'); ?>

        <style media="screen">
            .rpt-review-notice {
                padding: 15px 0;
                background-color: #fff;
                border-radius: 3px;
                margin: 20px 20px 0 0;
                border-left: 4px solid transparent;
            }

            .rpt-review-notice:after {
                content: '';
                display: table;
                clear: both;
            }

            .rpt-review-thumbnail {
                float: left;
                line-height: 80px;
                text-align: center;
                width: 117px;
            }

            .rpt-review-thumbnail img {
                width: 118px;
                vertical-align: middle;
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
        </style>

        <div class="rpt-review-notice">
            <div class="rpt-review-thumbnail">
                <img src="<?php echo plugins_url('assets/images/rpt-logo.png', __FILE__); ?>" alt="">
            </div>
            <div class="rpt-review-text">

                <h3>
                    <?php _e('Leave A Review?', 'related-posts-thumbnails'); ?>
                </h3>

                <p>
                    <?php _e('We hope you\'ve enjoyed using Related Post Thumbnails! Would you consider leaving us a review on WordPress.org?', 'related-posts-thumbnails'); ?>
                </p>

                <ul class="rpt-review-ul">
                    <li>
                        <a href="https://wordpress.org/support/plugin/related-posts-thumbnails/reviews/?filter=5"
                           target="_blank"><span class="dashicons dashicons-external"></span>
                            <?php _e('Sure! I\'d love to!', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo $dismiss_url; ?>">
                            <span class="dashicons dashicons-smiley"></span>
                            <?php _e('I\'ve already left a review', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo $later_url; ?>">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Maybe Later', 'related-posts-thumbnails'); ?>
                        </a>
                    </li>

                    <li>
                        <a href="<?php echo $dismiss_url; ?>">
                            <span class="dashicons dashicons-dismiss"></span>
                            <?php _e('Never show again', 'related-posts-thumbnails'); ?>
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
     */
    function review_prending()
    {
        // delete_site_option( 'rpt_review_dismiss' );
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
     */
    private function review_dismissal()
    {
        //delete_option( 'rpt_review_dismiss' );
        if (!is_admin() || !current_user_can('manage_options') || !isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'rpt-review-nonce') || !isset($_GET['rpt_review_dismiss'])) {

            return;
        }

        add_option('rpt_review_dismiss', 'yes');
    }

    /**
     * [is_old_default_img Check the compatibility for old default image path.]
     *
     * @return boolean Return true if path is old.
     */
    function is_old_default_img()
    {
        if (get_option('relpoststh_default_image') !== $this->default_image) {

            $chunks = explode('/', get_option('relpoststh_default_image'));
            if (in_array('related-posts-thumbnails', $chunks)) {
                return true;
            }
        }
    }

    /**
     * Get the configured default thumbnail URL based on the selected type.
     *
     * @return string Default image URL.
     */
    public function rpt_get_default_image_url() {
        $type = get_option( 'relpoststh_default_image_type', $this->default_image_type );

        if ( 'url' === $type ) {
            $remote_url = get_option( 'relpoststh_default_image_url', '' );

            if ( ! empty( $remote_url ) ) {
                return esc_url( $remote_url );
            }
        }

        return get_option( 'relpoststh_default_image', $this->default_image );
    }

    /**
     * Automatically displaying related posts under post body
     *
     * @param $content
     *
     * @return void
     */
    function auto_show($content)
    {
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

        if ( wp_is_mobile() && '1' === get_option( 'relpoststh_mobile_view', '0' ) ) {
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
        $priority = (int) apply_filters( 'rpt_content_prioirty', 10 );

        if ( get_option( 'relpoststh_auto', $this->auto ) && ! $this->rpt_is_author_related_enabled() && ! $this->rpt_should_skip_related_output() ) {
            add_filter( 'the_content', array( $this, 'auto_show' ), $priority );
        }

        if ( $this->rpt_is_author_related_enabled() && ! $this->rpt_should_skip_related_output() ) {
            add_filter( 'the_content', array( $this, 'rpt_author_related_show' ), $priority );
        }
    }

    /**
     * Whether author-related auto display is the active mode.
     *
     * @return bool
     * @since 5.0.0
     */
    public function rpt_is_author_related_enabled() {
        return '1' === get_option( 'relpoststh_author_related', $this->author_related ) && $this->rpt_has_multiple_publishers();
    }

    /**
     * Whether the site has more than one user who can publish posts.
     *
     * @return bool
     * @since 5.0.0
     */
    public function rpt_has_multiple_publishers() {
        if ( ! did_action( 'init' ) ) {
            return false;
        }

        static $request_cache = null;

        if ( null !== $request_cache ) {
            return $request_cache;
        }

        $stored = get_transient( RPT_MULTIPLE_PUBLISHERS_TRANSIENT );

        if ( false !== $stored ) {
            $request_cache = (bool) $stored;

            return $request_cache;
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

        return $request_cache;
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

        if ( wp_is_mobile() && '1' === get_option( 'relpoststh_mobile_view', '0' ) ) {
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
     *
     */
    function get_html($show_top = false)
    {
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
     */
    public function rpt_query_related_post_ids( $post_id, $query_args, &$debug = '' ) {
        $post_id = absint( $post_id );

        if ( $post_id <= 0 ) {
            return array();
        }

        $posts_number = absint( $query_args['posts_number'] ?? 0 );
        $sort_by      = rpt_sanitize_related_sort_by( $query_args['sort_by'] ?? 'rand()' );
        $author_id    = absint( $query_args['author_id'] ?? 0 );
        $exclude      = $query_args['exclude'] ?? '';

        if ( $posts_number <= 0 ) {
            return array();
        }

        $relation              = get_option( 'relpoststh_relation', $this->relation );
        $categories_show_all   = get_option( 'relpoststh_show_categoriesall', get_option( 'relpoststh_categoriesall', $this->categories_all ) );
        $thsource              = get_option( 'relpoststh_thsource', $this->thsource );
        $onlywiththumbs        = ( current_theme_supports( 'post-thumbnails' ) && 'post-thumbnails' === $thsource )
            ? get_option( 'relpoststh_onlywiththumbs', false )
            : false;

        global $wpdb;

        $debug .= "Relation: $relation; All categories: $categories_show_all;";
        $use_filter = ( '1' !== $categories_show_all || 'no' !== $relation );

        if ( $use_filter ) {
            $query_objects = "SELECT distinct object_id FROM $wpdb->term_relationships WHERE 1=1 ";

            if ( 'no' !== $relation ) {
                if ( 'categories' === $relation ) {
                    $taxonomy = array( 'category' );
                } elseif ( 'tags' === $relation ) {
                    $taxonomy = array( 'post_tag' );
                } elseif ( 'custom' === $relation ) {
                    $taxonomy = get_option( 'relpoststh_custom_taxonomies', $this->custom_taxonomies );
                } else {
                    $taxonomy = array( 'category', 'post_tag' );
                }

                $object_terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

                if ( empty( $object_terms ) || ! is_array( $object_terms ) ) {
                    $debug .= __( 'No taxonomy terms to get posts;', 'related-posts-thumbnails' );

                    return array();
                }

                $query           = "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE term_id in ('" . implode( "', '", $object_terms ) . "')";
                $object_taxonomy = $wpdb->get_results( $query );
                $object_taxonomy_a = array();

                if ( count( $object_taxonomy ) > 0 ) {
                    foreach ( $object_taxonomy as $item ) {
                        $object_taxonomy_a[] = $item->term_taxonomy_id;
                    }
                }

                $query_objects .= " AND term_taxonomy_id IN ('" . implode( "', '", $object_taxonomy_a ) . "') ";
            }

            if ( '1' !== $categories_show_all ) {
                $select_terms = get_option( 'relpoststh_show_categories', get_option( 'relpoststh_categories' ) );

                if ( empty( $select_terms ) || ! is_array( $select_terms ) ) {
                    $debug .= __( 'No categories were selected;', 'related-posts-thumbnails' );

                    return array();
                }

                $query           = "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE term_id in ('" . implode( "', '", $select_terms ) . "')";
                $taxonomy        = $wpdb->get_results( $query );
                $filter_taxonomy_a = array();

                if ( count( $taxonomy ) > 0 ) {
                    foreach ( $taxonomy as $item ) {
                        $filter_taxonomy_a[] = $item->term_taxonomy_id;
                    }
                }

                if ( 'no' !== $relation ) {
                    $query_objects .= " AND object_id IN (SELECT distinct object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id IN ('" . implode( "', '", $filter_taxonomy_a ) . "') )";
                } else {
                    $query_objects .= " AND term_taxonomy_id IN ('" . implode( "', '", $filter_taxonomy_a ) . "')";
                }
            }

            $relationships   = $wpdb->get_results( $query_objects );
            $related_objects = array();

            if ( count( $relationships ) > 0 ) {
                foreach ( $relationships as $item ) {
                    $related_objects[] = $item->object_id;
                }
            }
        }

        $custom_relations = apply_filters( 'rpt_custom_relationship', array( get_post_type( $post_id ) ) );

        if ( ! is_array( $custom_relations ) ) {
            $custom_relations = array( $custom_relations );
        }

        $selected_post_type = '';

        foreach ( $custom_relations as $checked_post_type ) {
            if ( in_array( $checked_post_type, get_post_types(), true ) ) {
                $selected_post_type .= "'" . esc_html( $checked_post_type ) . "',";
            }
        }

        $checked_post_type = rtrim( $selected_post_type, ',' );
        $query             = "SELECT ID FROM $wpdb->posts ";
        $where             = ' WHERE post_type IN (' . $checked_post_type . ") AND post_status = 'publish' AND ID<>" . $post_id;
        $startdate         = get_option( 'relpoststh_startdate' );

        if ( ! empty( $startdate ) && preg_match( '/^\d\d\d\d-\d\d-\d\d$/', $startdate ) ) {
            $debug .= "Startdate: $startdate;";
            $where .= " AND post_date >= '" . $startdate . "'";
        }

        if ( ! empty( $author_id ) ) {
            $where .= ' AND post_author = ' . $author_id;
            $debug .= 'Author filter: ' . $author_id . ';';
        }

        if ( $use_filter ) {
            if ( empty( $related_objects ) ) {
                $debug .= __( 'No posts matching relationships criteria;', 'related-posts-thumbnails' );

                return array();
            }

            $where .= " AND ID IN ('" . implode( "', '", $related_objects ) . "')";
        }

        $join = '';

        if ( $onlywiththumbs ) {
            $debug .= 'Only with thumbnails;';
            $join   = " INNER JOIN $wpdb->postmeta ON ($wpdb->posts.ID = $wpdb->postmeta.post_id)";
            $where .= " AND $wpdb->postmeta.meta_key = '_thumbnail_id'";
        }

        $order_query  = ' ORDER BY ' . $sort_by;
        $limit_order  = $order_query . ' LIMIT ' . $posts_number;
        $random_posts = $wpdb->get_results( $query . $join . $where . $limit_order );

        if ( ! is_array( $random_posts ) || count( $random_posts ) < 1 ) {
            $debug .= __( 'No posts matching relationships criteria;', 'related-posts-thumbnails' );

            return array();
        }

        $posts_in = array();

        foreach ( $random_posts as $random_post ) {
            $posts_in[] = $random_post->ID;
        }

        if ( ! empty( $exclude ) ) {
            if ( is_array( $exclude ) ) {
                $exclude_post_ids = array_map( 'absint', $exclude );
            } else {
                $exclude_post_ids = array_map( 'absint', explode( ',', (string) $exclude ) );
            }

            $posts_in = array_values( array_diff( $posts_in, $exclude_post_ids ) );
        }

        return $posts_in;
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
     * @return void
     * @version 4.3.0
     *
     * @since 1.0.0
     */
    function get_thumbnails($show_top = false, $posts_number = '', $sort_by = '', $main_title = '', $exclude = '', $author_id = 0)
    {
        $output = '';
        $debug = 'Developer mode initialization; Version: ;' . RELATED_POSTS_THUMBNAILS_VERSION;
        $time = microtime( true );

        $amp_endpoint = (function_exists('is_amp_endpoint') && is_amp_endpoint()) ? true : false;

        $show_category = get_option('relpoststh_show_taxonomy');

        // Stop execution if RPT is disabled in AMP view
        if (($amp_endpoint) && ('disable' === apply_filters('rpth_amp', true))) {

            $debug .= 'AMP view disabled';
            return $this->finish_process('', $debug, $time);
        }

        $posts_number_opt = get_option('relpoststh_number', $this->number);
        $posts_number = !empty($posts_number) ? $posts_number : $posts_number_opt;
        $height = '';
        $width = '';

        $sort_by_opt = get_option('rpt_post_sort') == 'latest' ? 'post_date DESC' : 'rand()';
        $sort_by = rpt_sanitize_related_sort_by( ! empty( $sort_by ) ? $sort_by : $sort_by_opt );

        // rpt_content_align: add content allignment class; clases are: relpost-align-left, relpost-align-right and relpost-align-center
        $output = '<!-- relpost-thumb-wrapper -->';
        $output .= '<div class="relpost-thumb-wrapper">';
        $output .= '<!-- filter-class -->';
        $output .= '<div class="relpost-thumb-container' . apply_filters('rpt_content_align', '') . '">';
        $alt = '';

        if ($posts_number <= 0) { // return nothing if this parameter was set to <= 0
            $output = '';
            return $this->finish_process($output, $debug . 'Posts number is 0;', $time);
        }

        $id = get_the_ID();
        $poststhname = get_option('relpoststh_poststhname', $this->poststhname);
        $text_length = get_option('relpoststh_textlength', $this->text_length) ? get_option('relpoststh_textlength', $this->text_length) : 0;
        $excerpt_length = get_option('relpoststh_excerptlength', $this->excerpt_length) ? get_option('relpoststh_excerptlength', $this->text_length) : 0;
        $thsource = get_option('relpoststh_thsource', $this->thsource);
        $onlywiththumbs = ( current_theme_supports( 'post-thumbnails' ) && 'post-thumbnails' === $thsource )
            ? (bool) get_option( 'relpoststh_onlywiththumbs', false )
            : false;
        $post_type = get_post_type();
        $order_query = ' ORDER BY ' . $sort_by;

        $query_args = array(
            'posts_number' => $posts_number,
            'sort_by'      => $sort_by,
            'author_id'    => absint( $author_id ),
            'exclude'      => $exclude,
        );

        $posts_in      = false;
        $cache_context = '';

        if ( rpt_is_related_cache_enabled() && $id ) {
            $cache_context = rpt_build_related_cache_context( $query_args );
            $cached_ids    = rpt_get_cached_related_ids( $id, $cache_context );

            if ( is_array( $cached_ids ) ) {
                $validation = rpt_validate_cached_related_ids( $cached_ids, $posts_number, $onlywiththumbs );

                if ( ! $validation['requery'] ) {
                    $posts_in = $validation['ids'];
                }
            }
        }

        if ( false === $posts_in ) {
            $posts_in = $this->rpt_query_related_post_ids( $id, $query_args, $debug );

            if ( empty( $posts_in ) ) {
                $output = '';
                return $this->finish_process( $output, $debug, $time );
            }

            if ( rpt_is_related_cache_enabled() && $cache_context ) {
                rpt_set_cached_related_ids( $id, $cache_context, $posts_in );
            }
        }

        /**
         *
         * Filter rpt_exclude_post to exclude post from RPT thumbnails
         *
         * @since 1.9.0
         * @version 4.3.0
         */
        $exclude_by_filter = apply_filters('rpt_exclude_post', '');

        if ($exclude_by_filter !== '') {
            $regex = '/^\d+(?:,\d+)*$/';
            $exclude_by_filter = preg_match($regex, $exclude_by_filter) ? $exclude_by_filter : array();
            $exclude_by_filter_ids = explode(",", $exclude_by_filter);
            $posts_in = array_diff($posts_in, $exclude_by_filter_ids);

        }

        if ( empty( $posts_in ) ) {
            $output = '';
            return $this->finish_process( $output, $debug . 'No posts found after exclusions;', $time );
        }

        global $wpdb;

        $query = "SELECT ID, post_content, post_excerpt, post_title FROM $wpdb->posts WHERE ID IN ('" . implode("', '", $posts_in) . "') $order_query ";
        $posts = $wpdb->get_results($query);

        if (!(is_array($posts) && count($posts) > 0)) { // no posts
            $debug .= 'No posts found;';
            $output = '';
            return $this->finish_process($output, $debug, $time);
        } else {
            $debug .= 'Found ' . count($posts) . ' posts;';
            RPT_Frontend_Assets::rpt_enqueue( $this );
        }

        /* Calculating sizes */
        if ($thsource == 'custom-field') {
            $debug .= 'Custom sizes;';
            $width = get_option('relpoststh_customwidth', $this->custom_width);
            $height = get_option('relpoststh_customheight', $this->custom_height);
        } else {
            // post-thumbnails source
            if ($poststhname == 'thumbnail' || $poststhname == 'medium' || $poststhname == 'large') { // get thumbnail size for basic sizes
                $debug .= 'Basic sizes;';
                $width = get_option("{$poststhname}_size_w");
                $height = get_option("{$poststhname}_size_h");
            } elseif (current_theme_supports('post-thumbnails')) { // get sizes for theme supported thumbnails
                global $_wp_additional_image_sizes;
                if (isset($_wp_additional_image_sizes[$poststhname])) {
                    $debug .= 'Additional sizes;';
                    $width = $_wp_additional_image_sizes[$poststhname]['width'];
                    $height = $_wp_additional_image_sizes[$poststhname]['height'];
                } else {
                    $debug .= 'No additional sizes;';
                }
            }
        }

        // displaying square if one size is not cropping
        if ($height == 9999) {
            $height = $width;
        }

        if ($width == 9999) {
            $width = $height;
        }
        // theme is not supporting but settings were not changed
        if (empty($width)) {
            $debug .= 'Using default width;';
            $width = get_option('thumbnail_size_w');
        }
        if (empty($height)) {
            $debug .= 'Using default height;';
            $height = get_option('thumbnail_size_h');
        }

        $debug .= 'Got sizes ' . $width . 'x' . $height . ';';
        $title_tag = apply_filters('relpoststh_title_tag', get_option( 'relpoststh_title_tag', 'h2' ) );

        // rendering related posts HTML
        if ($show_top) {
            if (!empty($main_title)) {

                $output .= '<' . esc_attr( $title_tag ) . ' class="relpoststh-block-title">' . esc_html( $main_title ) . '</' . esc_attr( $title_tag ) . '>';
            } else {
                $top_text = stripslashes(get_option('relpoststh_top_text', $this->top_text));
                $output .= stripslashes(apply_filters('rpt_top_text', $top_text));
            }
        }

        $relpoststh_output_style = get_option('relpoststh_output_style', $this->output_style);
        $relpoststh_show_date    = get_option('relpoststh_show_date', '0');
        $relpoststh_date_format  = get_option('relpoststh_date_format', $this->format);

        $relpoststh_cleanhtml    = get_option('relpoststh_cleanhtml', 0);
        $text_height             = get_option('relpoststh_textblockheight', $this->text_block_height);
		$column = get_option('relpoststh_column', $this->relpoststh_column) ?: "3";
		$column_t = get_option('relpoststh_column_t', $this->relpoststh_column_t) ?: "2";
		$column_m = get_option('relpoststh_column_m', $this->relpoststh_column_m) ?: "2";
		$relpoststh_image_size = get_option('relpoststh_image_size', $this->relpoststh_image_size) ?: "1/1";
		
		$column_layout = '';
		if($column !== ''){
			$column_layout = "relpost-block-column-layout";
		}


        if ($relpoststh_output_style == 'list') {
            $output .= '<!-- related_posts_thumbnails -->';
            $output .= '<ul id="related_posts_thumbnails"';
            if (!$relpoststh_cleanhtml) {
                $output .= ' style="list-style-type:none; list-style-position: inside; padding: 0; margin:0"';
            }
            $output .= '>';
        } else {

            $output .= '<div style="clear: both"></div>';

            // $output .= '<!-- related-posts-nav -->';
            // $output .=  '<ul class="related-posts-nav">'; //open blocks ul

            $output .= '<div style="clear: both"></div>';

            $output .= '<!-- relpost-block-container -->';
            $output .= '<div class="relpost-block-container ' . $column_layout . '" style="--relposth-columns: '. $column .';--relposth-columns_t: '. $column_t .'; --relposth-columns_m: '. $column_m .'">'; // open relpost-block-container div
        }

        foreach ($posts as $post) {
            $image = '';
            $url = '';
            $alt = '';
            $category_list = '';
			$post_id = $post->ID;
            $taxonomies = get_object_taxonomies($post_type);

            /**
             * Show the Categories names of a post in related post thumbnails.
             *
             * @since 2.2.0
             * @version 4.3.0
             */
            if ($show_category) {
				$category_list = $this->rpt_get_post_category_list( $show_category, $taxonomies, $post, $post_type );
            }

            // Setting's option to show the first image of the article, if no featured image is set
            $articlefirstimage = get_option('relpoststh_articlefirstimage', '1');
            if ($thsource == 'custom-field') {
                $custom_field = get_option('relpoststh_customfield', $this->custom_field);
                $custom_field_meta = get_post_meta( $post_id, $custom_field );
                if (empty($custom_field)) {
                    $debug .= 'No custom field specifield, using default thumbnail image;';
                    $url = $this->default_image;
                } elseif (empty($custom_field_meta) && apply_filters('rpt_remove_empty_cfield', false)) {
                    $debug .= 'Custom field meta is empty, using rpt_remove_empty_cfield filter;';
                    continue;
                } elseif (empty($custom_field_meta)) {
                    $debug .= 'Custom field meta is empty, using default thumbnail image;';
                    $url = $this->default_image;
                } else {
                    $debug .= 'Using custom field;';

                    /**
                     * Fix the single URL or Image object.
                     *
                     * @since 2.0.3
                     * @version 2.0.4
                     */
                    if (is_array($custom_field_meta) && isset($custom_field_meta[0])) {
                        // If your post meta has an attachment ID instead of string URL.
                        $url = wp_get_attachment_image_src($custom_field_meta[0]) ? wp_get_attachment_image_src($custom_field_meta[0])[0] : $this->default_image;

                        /**
                         * Check if the custom field has string or int saved in it.
                         * Upon this condition serve the image accordingly.
                         *
                         * @since 2.0.4
                         */
                        $url = isset($custom_field_meta[0]) && (int)$custom_field_meta[0] ? $url : $custom_field_meta[0];
                    } else {
                        $url = isset($custom_field_meta) && !empty($custom_field_meta) ? $custom_field_meta : $this->default_image;
                    }

                    $theme_resize_url = get_option('relpoststh_theme_resize_url', '');

                    if (strpos($url, '/wp-content') !== false) {
                        $url = substr($url, strpos($url, '/wp-content'));
                    }

                    if (!empty($theme_resize_url)) {
                        $url = $theme_resize_url . '?src=' . $url . '&w=' . $width . '&h=' . $height . '&zc=1&q=90';
                    }
                }
            } else {

                // using built in WordPress Thumbnails Feature
                if (current_theme_supports('post-thumbnails')) {

                    $post_thumbnail_id = get_post_thumbnail_id( $post_id );
                    $debug .= 'Post-thumbnails enabled in theme;';

                    if (!(empty($post_thumbnail_id) || $post_thumbnail_id === false)) { // post has thumbnail
                        $debug .= 'Post has thumbnail ' . $post_thumbnail_id . ';';
                        $debug .= 'Postthname: ' . $poststhname . ';';
                        $image = wp_get_attachment_image_src($post_thumbnail_id, $poststhname);
                        $alt = get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true);
                        /**
                         * Add the image check if found.
                         *
                         * @since 3.0.3
                         */
                        $url = !is_bool($image) && isset($image[0]) ? $image[0] : false;
                    } else {
                        $debug .= 'Post has no thumbnail;';
                    }
                    
                }

            }

            if (strpos($url, '/') === 0) {
                $url = get_bloginfo('url') . $url;
                $debug .= 'Relative url: ' . $url . ';';
            }

            // Get the first image from the post body if the option is enabled
            if ( empty($url) && $articlefirstimage === '1' ) {
                $debug .= 'Getting image from post body;';
                $wud = wp_upload_dir();

                // search the first uploaded image in content
                preg_match_all('|<img.*?src=[\'"](' . $wud['baseurl'] . '.*?)[\'"].*?>|i', $post->post_content, $matches);

                if (isset($matches) && isset($matches[1][0])) {
                    $image = $matches[1][0];
                    $html = $matches[0][0];

                    if (!empty($html)) {
                        preg_match('/alt="([^"]*)"/i', $html, $array);

                        if (!empty($array) && is_array($array)) {
                            $explode_tag = explode('"', $array[0]);
                            $alt = $explode_tag[1];
                        }
                    }

                } else {
                    $debug .= 'No image was found;';
                }

                if (strlen(trim($image)) > 0) {

                    $image_sizes = @getimagesize($image);

                    if ($image_sizes === false) {
                        $debug .= 'Unable to determine parsed image size';
                    }

                    if (($image_sizes !== false && isset($image_sizes[0])) && $image_sizes[0] == $width) {
                        // if this image is the same size
                        $debug .= 'Image used is the required size;';
                        $url = $image;
                    } elseif (apply_filters('rpt_prevent_img_size_check', false) && $image_sizes[0] < $width) {
                        // if this image is smaller than required size
                        $debug .= 'Image used is smaller than the required size, rpt_prevent_img_size_check filter is active;';
                        $url = $image;
                    } else {
                        $debug .= 'Changing image according to Wordpress standards;';
                        $url = $image;
                    }

                } else {
                    $debug .= 'Found wrong formatted image: ' . $image . ';';
                }
            }

            // parsed URL is empty or no image found
            if (empty($url)) {
                $debug .= 'Image URL: ' . $url . ';';
                $debug .= 'Image is empty or no file. Using default image;';
                $url = $this->rpt_get_default_image_url();
            }

            $title = $this->process_text_cut($post->post_title, $text_length);
            $post_excerpt = (empty($post->post_excerpt)) ? $post->post_content : $post->post_excerpt;
            $excerpt = $this->process_text_cut($post_excerpt, $excerpt_length);
            $aria_label = 'aria-label="' . esc_attr($alt) . '"';

            if (empty($alt)) {
                $alt = str_replace('"', '', $title);
                $aria_label = 'aria-hidden="true"';
            }
			if ( ! empty( $title) ) {
                 $title = '<' . esc_attr( $title_tag ) . ' class="relpost_card_title">' . esc_html( $title ) . '</' . esc_attr( $title_tag ) . '>';

			}
            if (!empty($excerpt)) {
                $excerpt = '<div class="relpost_card_exerpt">' . $excerpt . '</div>';
            }

            $fontface = str_replace( '"', "'", stripslashes( get_option( 'relpoststh_fontfamily', $this->font_family ) ) );
			$debug .= 'Using title with size ' . $text_length . '. Using excerpt with size ' . $excerpt_length . ';';
			$after_content = apply_filters( 'rpth_after_content', '', $post );

                $date_output = '';

                /**
                 * Get the date format from the settings.
                 *
                 * @since 1.9.3
                 */
                if ('0' !== $relpoststh_show_date) {
                    $date = get_the_date($relpoststh_date_format, $post_id );
                    $date_output = '<span class="rpth_list_date">' . $date . '</span>';
                }

                if ( $show_category ) {
					$category_list = $this->rpt_get_post_category_list( $show_category, $taxonomies, $post, $post_type );
                }

                if ($relpoststh_output_style == 'list') {
                    $link = get_permalink( $post_id );
                    $output .= '<li ';

                    // if ( !$relpoststh_cleanhtml ) {
                    // $output .= ' onmouseout="this.style.backgroundColor=\'' . get_option( 'relpoststh_background', $this->background ) . '\'"';
                    // }

                    $output .= '>';
                    $output .= '<a href="' . $link . '"><img class="relpost-post-image lazy-load" alt="' . esc_attr( $alt ) . '" data-src="' . esc_url( $url ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" ';

                    if ( $relpoststh_image_size ) {
                        $output .= 'style="aspect-ratio:' . esc_attr( $relpoststh_image_size ) . '"';
                    }

                    $output .= '/></a>';

                    if ($text_height != '0') {
                        $output .= '<a href="' . $link . '" class="relpost_content"';

                        if (!$relpoststh_cleanhtml) {
                            $output .= ' style="width: ' . $width . 'px;height: ' . $text_height . 'px; font-family: ' . $fontface . '; "';
                        }


                        // $output .= '><span class="rpth_list_content">' . $title . $excerpt . '</span>' . $date . '</a></li>';
                        $output .= '><span class="rpth_list_content">' . $title . $excerpt . $date_output . $category_list . '</span>' . $after_content . '</a></li>';
                    }
                } else {
                    //if lazy-load is not activated
                    $rpt_single_background = apply_filters('rpt-single-background', 'data-bg="' . esc_url( $url ) . '" style="background: transparent no-repeat scroll 0% 0%; width: ' . esc_attr( $width ) . 'px; height: ' . esc_attr( $height ) . 'px; aspect-ratio: ' . esc_attr( $relpoststh_image_size ) . ';"');

                    //if lazy-load is activated.
                    $rpt_lazy_single_background = apply_filters('rpt-lazy-loading', false);

                    $rpt_anchor_attrs = array(
                        'class' => '',
                        'target' => false,
                    );

                    /**
                     * Filter to enhance the related post thumbnail anchor attribute such as open post in a new tab.
                     *
                     * @param int $post ->ID Current post ID.
                     * @param array $rpt_anchor_attrs array of the attributes.
                     *
                     * @since 1.9.2
                     */
                    $rpt_anchor_attr_filter = (array)apply_filters('relpost_anchor_attr', $post_id, $rpt_anchor_attrs);

                    /**
                     * Array Containing Allowed attributes.
                     */
                    $allowed_anchor_attrs = array('title', 'class', 'target');

                    // Pattern for data-* attribute.
                    $data_attr_ptrn = "/data-/i";

                    $relpost_attributes = 'class="relpost-block-single';

                    // Attr sets for class including default class.
                    if (array_key_exists('class', $rpt_anchor_attr_filter)) {

                        // Class attributes in string.
                        $class_attrs_str = esc_attr(wp_unslash($rpt_anchor_attr_filter['class']));
                        // Class attributes in array.
                        $class_attrs = explode(" ", $class_attrs_str);

                        foreach ($class_attrs as $value) {
                            if ('relpost-block-single' !== $value && !empty($value)) {
                                $relpost_attributes .= ' ' . $value;
                            }
                        }
                    }
                    $relpost_attributes .= '" ';

                    foreach ($rpt_anchor_attr_filter as $rel_post_a_attr => $value) {

                        $value = esc_attr(wp_unslash($value));
                        $rel_post_a_attr = esc_attr(wp_unslash($rel_post_a_attr));

                        if (false !== $value && !empty($value) && $rel_post_a_attr !== 'class' && (in_array($rel_post_a_attr, $allowed_anchor_attrs) || preg_match($data_attr_ptrn, $rel_post_a_attr))) {
                            $relpost_attributes .= $rel_post_a_attr . '="' . $value . '"';
                        }
                    }

                    $output .= '<a href="' . get_permalink( $post_id ) . '"' . $relpost_attributes . '>';

                    $output .= '<div class="relpost-custom-block-single">';
                    if ($rpt_lazy_single_background) {
                        $output .= '<img loading="lazy" class="relpost-block-single-image" alt="' . esc_attr($alt) . '"  src="' . esc_url($url) . '" style="aspect-ratio:'.$relpoststh_image_size.'" style="aspect-ratio:'.$relpoststh_image_size.'">' . $date_output . $category_list . '</img>';
                    } else {
                        $output .= '<div class="relpost-block-single-image rpt-lazyload" ' . $aria_label . ' role="img" ' . $rpt_single_background . '></div>';
                    }
                    $output .= '<div class="relpost-block-single-text"  style="height: ' . ($text_height) . 'px;font-family: ' . $fontface . ';  font-size: ' . get_option('relpoststh_fontsize', $this->font_size) . 'px;  color: ' . get_option('relpoststh_fontcolor', $this->font_color) . ';">' . $title . $excerpt . $date_output . $category_list . '</div>';
                    $output .= $after_content;
                    // $output .= $date;
                    $output .= '</div>';
                    $output .= '</a>';
                }

        } // end foreach

        if ($relpoststh_output_style == 'list') {
            $output .= '</ul>';
            $output .= '<!-- close related_posts_thumbnails -->';
        } else {
            $output .= '</div>';
            $output .= '<!-- close relpost-block-container -->';
            // $output .= '</ul>';
            // $output .= '<!-- close related-posts-nav -->';
        }

        $output .= '<div style="clear: both"></div>';

        $output .= '</div>';
        $output .= '<!-- close filter class -->';

        $output .= '</div>';
        $output .= '<!-- close relpost-thumb-wrapper -->';

        return $this->finish_process($output, $debug, $time);
    }

    /**
     * Show Related Post Categories.
     *
     * @param int $id The ID of post.
     *
     * @return string $category_list The structure of category list.
     *
     * @since 2.2.0
     */
    function relpoststh_category_list($id, $taxonomy, $post_type)
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
     * @version 3.0.1
     */
    function relpoststh_get_featured_category($post_ID, $taxonomy_name = 'category', $post_type = 'post')
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

    // function is_url_404( $url ) {
    //   $response = wp_remote_request( $url,
    //     array(
    //       'method'     => 'GET'
    //     )
    //   );

    //   return $response['response']['code'];
    // }

    /**
     * This will add debugging information in HTML source
     *
     * @param $output
     * @param  $debug
     * @param int $time time took to create the thumbnails
     *
     * @return $output debugged information
     */
    function finish_process($output, $debug, $time)
    {

        $devmode = get_option('relpoststh_devmode', $this->devmode);

        if ($devmode) {
            $time = microtime(true) - $time;
            $debug .= "Plugin execution time: $time sec;";
            $output .= '<!-- ' . $debug . ' -->';
        }

        return $output;
    }

    function process_text_cut($text, $length)
    {

        if ($length == 0) {
            return '';
        } else {
            $text = strip_tags(strip_shortcodes($text));

            if (function_exists('mb_strlen')) {
                return ((mb_strlen($text) > $length) ? mb_substr($text, 0, $length) . '...' : $text);
            } else {
                return ((strlen($text) > $length) ? substr($text, 0, $length) . '...' : $text);
            }
        }
    }

    /**
     * Function to check th options to show the thumbnails according to the post types, categories etc.
     *
     * @return boolean
     */
    function is_relpoststh_show()
    {
        // Checking display options
        if (!is_single() && get_option('relpoststh_single_only', $this->single_only)) { // single only
            return false;
        }
        // Check post type
        $post_types = get_option('relpoststh_post_types', $this->post_types);
        $post_type = get_post_type();

        if (!in_array($post_type, $post_types)) {
            return false;
        }
        // Check categories
        $id = get_the_ID();
        $categories_all = get_option('relpoststh_categoriesall', $this->categories_all);

        if ($categories_all != '1') { // only specific categories were selected

            $post_categories = wp_get_object_terms($id, array('category'), array('fields' => 'ids'));
            $relpoststh_categories = get_option('relpoststh_categories');

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
     */
    public function rpt_admin_bar_cache_purge( $admin_bar ) {
        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) || ! rpt_is_related_cache_enabled() ) {
            return;
        }

        $admin_bar->add_node(
            array(
                'id'    => 'rpt-purge-cache',
                'title' => esc_html__( 'Purge RPT Cache', 'related-posts-thumbnails' ),
                'href'  => wp_nonce_url(
                    admin_url( 'admin-post.php?action=rpt_purge_cache' ),
                    'rpt_purge_cache'
                ),
            )
        );
    }

    /**
     * Handle admin bar cache purge requests.
     *
     * @return void
     * @since 5.0.0
     */
    public function rpt_handle_admin_purge_cache() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No access', 'related-posts-thumbnails' ) );
        }

        check_admin_referer( 'rpt_purge_cache' );

        rpt_purge_all_related_cache();

        $redirect = wp_get_referer();

        if ( ! $redirect ) {
            $redirect = admin_url( 'admin.php?page=related-posts-thumbnails' );
        }

        wp_safe_redirect( add_query_arg( 'rpt_cache_purged', '1', $redirect ) );
        exit;
    }

    /**
     * Show cache purge notices in admin.
     *
     * @return void
     * @since 5.0.0
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
     */
    function admin_menu()
    {
        add_menu_page(__('Related Posts Thumbnails', 'related-posts-thumbnails'), __('Related Posts', 'related-posts-thumbnails'), 'administrator', 'related-posts-thumbnails', array($this, 'admin_interface'), 'dashicons-screenoptions');
        add_submenu_page(
            'related-posts-thumbnails',
            __( 'Activate', 'related-posts-thumbnails' ),
            ' ',
            'manage_options',
            'rpt-optin',
            array( $this, 'render_optin' )
        );
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
	private function rpt_get_post_category_list( $show_category, $taxonomies, $post, $post_type ) {
		// Ensure $post is a valid post ID or object
		$post_id = is_object( $post ) && isset( $post->ID) ? $post->ID : ( is_numeric( $post ) ? intval( $post ) : 0 );

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
     */
    function admin_interface()
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
     */
    function display_categories_list($categoriesall, $categories, $selected_categories, $all_name, $specific_name)
    { ?>
        <input style="display:none" id="<?php echo esc_attr($all_name) . '_check'; ?>" class="select_all"
               type="checkbox" name="<?php echo esc_attr($all_name); ?>" value="1" <?php
        if ($categoriesall == '1') {
            echo 'checked="checked"';
        } ?>
        />
        <label style="display:none" for="<?php echo esc_attr($all_name); ?>">
            <?php _e('All', 'related-posts-thumbnails'); ?>
        </label>
        <div class="select_specific" <?php echo esc_attr($specific_name);
        if ($categoriesall == '1'): ?>
            style="display:none"
        <?php endif; ?> >
        </div>
        <select class="chosen-select <?php echo esc_attr($all_name); ?>"
                data-placeholder="<?php _e('Select Categories', 'related-posts-thumbnails'); ?>"
                id="<?php echo esc_attr($all_name); ?>" name="<?php echo esc_attr($specific_name); ?>[]" multiple>
            <option value="0" <?php if ($categoriesall == '1') {
                echo 'selected="selected"';
            } ?>><?php _e('All', 'related-posts-thumbnails'); ?></option>
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


// Include frontend assets handler.
include_once plugin_dir_path( __FILE__ ) . 'inc/rpt-assets.php';
RPT_Frontend_Assets::rpt_init( __FILE__ );

// Include Widget File.
include_once plugin_dir_path(__FILE__) . 'inc/rpt-widget.php';
// Include Blocks File.
include_once plugin_dir_path(__FILE__) . 'inc/rpt-blocks.php';

?>
