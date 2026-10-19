<?php
/**
 * Enqueue block editor only JavaScript and CSS.
 *
 * @package Related_Posts_Thumbnails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue block editor assets.
 *
 * Block assets are not part of the minified frontend pipeline; paths stay fixed.
 *
 * @return void
 */
function rpt_enqueue_block_editor_assets() {
	$block_path = '../assets/blocks/js/blocks.editor.js';
	$style_path = '../assets/blocks/css/blocks.editor.min.css';

	wp_enqueue_script(
		'rpt-blocks-js',
		plugins_url( $block_path, __FILE__ ),
		array( 'wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor' ),
		RELATED_POSTS_THUMBNAILS_VERSION,
		true
	);

	wp_enqueue_style(
		'rpt-blocks-editor-css',
		plugins_url( $style_path, __FILE__ ),
		array(),
		RELATED_POSTS_THUMBNAILS_VERSION
	);
}

add_action( 'enqueue_block_editor_assets', 'rpt_enqueue_block_editor_assets' );

/**
 * Create related posts block category.
 *
 * @param array $categories Existing block categories.
 * @return array
 */
function rpt_block_category( $categories ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug'  => 'related-post-thumbnails-blocks',
				'title' => __( 'Related Posts Thumbnails', 'related-posts-thumbnails' ),
			),
		)
	);
}

if ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
	add_filter( 'block_categories_all', 'rpt_block_category', 10, 2 );
} else {
	add_filter( 'block_categories', 'rpt_block_category', 10, 2 );
}

/**
 * Register the dynamic block.
 *
 * @since 2.0.0
 *
 * @return void
 */
function register_rpt_dynamic_block() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type(
		'related-post-thumbnails/rpt-block',
		array(
			'attributes'      => array(
				'postNumber' => array(
					'type'    => 'number',
					'default' => 3,
				),
				'postSort'   => array(
					'type'    => 'string',
					'default' => 'random',
				),
				'mainTitle'  => array(
					'type'    => 'string',
					'default' => '',
				),
			),
			'render_callback' => 'render_rpt_dynamic_block',
		)
	);
}

add_action( 'plugins_loaded', 'register_rpt_dynamic_block' );

/**
 * Server-side render callback for the related posts block.
 *
 * Calls the shortcode handler with sanitized attributes instead of building
 * a shortcode string (avoids ] breakout / stored XSS via block attrs).
 *
 * @param array $atts Block attributes.
 * @return string
 */
function render_rpt_dynamic_block( $atts ) {
	$atts = is_array( $atts ) ? $atts : array();

	$posts_number = isset( $atts['postNumber'] ) ? absint( $atts['postNumber'] ) : 3;
	if ( $posts_number < 1 ) {
		$posts_number = 3;
	}

	$posts_sort = isset( $atts['postSort'] ) ? sanitize_key( (string) $atts['postSort'] ) : 'random';
	if ( ! in_array( $posts_sort, array( 'random', 'latest' ), true ) ) {
		$posts_sort = 'random';
	}

	$main_title = isset( $atts['mainTitle'] )
		? sanitize_text_field( (string) $atts['mainTitle'] )
		: '';

	global $related_posts_thumbnails;

	if ( ! $related_posts_thumbnails instanceof RelatedPostsThumbnails ) {
		return '';
	}

	return $related_posts_thumbnails->related_posts_shortcode(
		array(
			'posts_number' => (string) $posts_number,
			'posts_sort'   => $posts_sort,
			'main_title'   => $main_title,
			'exclude_post' => '',
		)
	);
}
