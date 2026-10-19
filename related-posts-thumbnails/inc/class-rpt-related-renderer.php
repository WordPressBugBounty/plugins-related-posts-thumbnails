<?php
/**
 * Related posts HTML renderer.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds related posts thumbnail markup.
 *
 * @since 6.0.0
 */
class RPT_Related_Renderer {

	/**
	 * Plugin instance.
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
	 * Render related posts thumbnails HTML.
	 *
	 * @param bool   $show_top     Whether to show the heading.
	 * @param string $posts_number Number of posts override.
	 * @param string $sort_by      Sort override.
	 * @param string $main_title   Optional heading.
	 * @param string $exclude      Excluded post IDs.
	 * @param int    $author_id    Optional author filter.
	 * @return string
	 * @since 6.0.0
	 */
	public function rpt_render( $show_top = false, $posts_number = '', $sort_by = '', $main_title = '', $exclude = '', $author_id = 0 ) {
		$output = '';
		$debug  = 'Developer mode initialization; Version: ' . RELATED_POSTS_THUMBNAILS_VERSION . ';';
		$time   = microtime( true );

		if ( $this->rpt_is_amp_output_disabled() ) {
			$debug .= 'AMP view disabled';

			return $this->plugin->finish_process( '', $debug, $time );
		}

		$posts_number = $this->rpt_resolve_posts_number( $posts_number );

		if ( $posts_number <= 0 ) {
			return $this->plugin->finish_process( '', $debug . 'Posts number is 0;', $time );
		}

		$sort_by = $this->rpt_resolve_sort_by( $sort_by );
		$id      = get_the_ID();
		$context = $this->rpt_get_render_context( $posts_number, $sort_by );

		$output  = '<!-- relpost-thumb-wrapper -->';
		$output .= '<div class="relpost-thumb-wrapper">';
		$output .= '<!-- filter-class -->';
		$output .= '<div class="relpost-thumb-container' . apply_filters( 'rpt_content_align', '' ) . '">';

		$query_args = array(
			'posts_number' => $posts_number,
			'sort_by'      => $sort_by,
			'author_id'    => absint( $author_id ),
			'exclude'      => $exclude,
		);

		$posts_in = $this->rpt_get_related_post_ids( $id, $query_args, $context['onlywiththumbs'], $debug );

		if ( empty( $posts_in ) ) {
			return $this->plugin->finish_process( '', $debug, $time );
		}

		$posts_in = $this->rpt_apply_exclude_filter( $posts_in );

		if ( empty( $posts_in ) ) {
			return $this->plugin->finish_process( '', $debug . 'No posts found after exclusions;', $time );
		}

		$posts = $this->rpt_fetch_related_posts( $posts_in, $sort_by, $debug );

		if ( empty( $posts ) ) {
			return $this->plugin->finish_process( '', $debug, $time );
		}

		RPT_Frontend_Assets::rpt_enqueue( $this->plugin );

		$sizes = $this->rpt_resolve_thumbnail_sizes( $context['thsource'], $context['poststhname'], $debug );
		$output = $this->rpt_render_heading( $output, $show_top, $main_title, $context['title_tag'] );
		$output = $this->rpt_open_items_container( $output, $context );
		$output = $this->rpt_render_items( $output, $posts, $sizes, $context, $debug );
		$output = $this->rpt_close_markup( $output, $context['output_style'] );

		return $this->plugin->finish_process( $output, $debug, $time );
	}

	/**
	 * Whether RPT output is disabled on AMP.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	private function rpt_is_amp_output_disabled() {
		$amp_endpoint = ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() );

		return $amp_endpoint && ( 'disable' === apply_filters( 'rpth_amp', true ) );
	}

	/**
	 * Resolve how many related posts to show.
	 *
	 * @param string|int $posts_number Override.
	 * @return int
	 * @since 6.0.0
	 */
	private function rpt_resolve_posts_number( $posts_number ) {
		$posts_number_opt = (int) rpt_settings()->rpt_get( 'general.number', $this->plugin->number );

		return ! empty( $posts_number ) ? (int) $posts_number : $posts_number_opt;
	}

	/**
	 * Resolve the ORDER BY fragment.
	 *
	 * @param string $sort_by Override.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_resolve_sort_by( $sort_by ) {
		$post_sort   = (string) rpt_settings()->rpt_get( 'general.post_sort', 'rand' );
		$sort_by_opt = ( 'latest' === $post_sort ) ? 'post_date DESC' : RPT_RELATED_SORT_RAND;

		return rpt_sanitize_related_sort_by( ! empty( $sort_by ) ? $sort_by : $sort_by_opt );
	}

	/**
	 * Shared settings used while rendering.
	 *
	 * @param int    $posts_number Number of posts.
	 * @param string $sort_by      Sort fragment.
	 * @return array<string, mixed>
	 * @since 6.0.0
	 */
	private function rpt_get_render_context( $posts_number, $sort_by ) {
		$thsource    = (string) rpt_settings()->rpt_get( 'thumbnails.thsource', $this->plugin->thsource );
		$text_length_raw    = (string) rpt_settings()->rpt_get( 'styles.text_length', $this->plugin->text_length );
		$excerpt_length_raw = (string) rpt_settings()->rpt_get( 'styles.excerpt_length', $this->plugin->excerpt_length );
		$column             = (string) rpt_settings()->rpt_get( 'styles.column', $this->plugin->relpoststh_column ) ?: '3';
		$column_t           = (string) rpt_settings()->rpt_get( 'styles.column_tablet', $this->plugin->relpoststh_column_t ) ?: '2';
		$column_m           = (string) rpt_settings()->rpt_get( 'styles.column_mobile', $this->plugin->relpoststh_column_m ) ?: '2';

		return array(
			'posts_number'   => $posts_number,
			'sort_by'        => $sort_by,
			'show_category'  => rpt_settings_bool( 'general.show_taxonomy' ),
			'poststhname'    => (string) rpt_settings()->rpt_get( 'thumbnails.poststhname', $this->plugin->poststhname ),
			'text_length'    => $text_length_raw ? (int) $text_length_raw : 0,
			'excerpt_length' => $excerpt_length_raw ? (int) $excerpt_length_raw : 0,
			'thsource'       => $thsource,
			'onlywiththumbs' => ( current_theme_supports( 'post-thumbnails' ) && 'post-thumbnails' === $thsource )
				? rpt_settings_bool( 'thumbnails.only_with_thumbs' )
				: false,
			'post_type'      => get_post_type(),
			'title_tag'      => apply_filters( 'relpoststh_title_tag', (string) rpt_settings()->rpt_get( 'general.title_tag', 'h2' ) ),
			'output_style'   => (string) rpt_settings()->rpt_get( 'styles.output_style', $this->plugin->output_style ),
			'show_date'      => rpt_settings_bool( 'general.show_date' ),
			'date_format'    => (string) rpt_settings()->rpt_get( 'general.date_format', $this->plugin->format ),
			'clean_html'     => rpt_settings_bool( 'styles.clean_html' ),
			'text_height'    => (string) rpt_settings()->rpt_get( 'styles.text_block_height', $this->plugin->text_block_height ),
			'column'         => $column,
			'column_t'       => $column_t,
			'column_m'       => $column_m,
			'image_size'     => (string) rpt_settings()->rpt_get( 'styles.image_size', $this->plugin->relpoststh_image_size ) ?: '1/1',
			'column_layout'  => ( '' !== $column ) ? 'relpost-block-column-layout' : '',
			'font_family'    => str_replace( '"', "'", stripslashes( (string) rpt_settings()->rpt_get( 'styles.font_family', $this->plugin->font_family ) ) ),
			'font_size'      => (string) rpt_settings()->rpt_get( 'styles.font_size', $this->plugin->font_size ),
			'font_color'     => (string) rpt_settings()->rpt_get( 'styles.font_color', $this->plugin->font_color ),
			'first_image'    => rpt_settings_article_first_image_enabled( true ),
		);
	}

	/**
	 * Load related IDs from cache or query.
	 *
	 * @param int              $id             Source post ID.
	 * @param array<string, mixed> $query_args Query arguments.
	 * @param bool             $onlywiththumbs Whether thumbnails are required.
	 * @param string           $debug          Debug output passed by reference.
	 * @return array<int>
	 * @since 6.0.0
	 */
	private function rpt_get_related_post_ids( $id, $query_args, $onlywiththumbs, &$debug ) {
		$posts_in      = false;
		$cache_context = '';

		if ( rpt_is_related_cache_enabled() && $id ) {
			$cache_context = rpt_build_related_cache_context( $query_args );
			$cached_ids    = rpt_get_cached_related_ids( $id, $cache_context );

			if ( is_array( $cached_ids ) ) {
				$validation = rpt_validate_cached_related_ids( $cached_ids, $query_args['posts_number'], $onlywiththumbs );

				if ( ! $validation['requery'] ) {
					$posts_in = $validation['ids'];
				}
			}
		}

		if ( false === $posts_in ) {
			$posts_in = $this->plugin->rpt_query_related_post_ids( $id, $query_args, $debug );

			if ( empty( $posts_in ) ) {
				return array();
			}

			if ( rpt_is_related_cache_enabled() && $cache_context ) {
				rpt_set_cached_related_ids( $id, $cache_context, $posts_in );
			}
		}

		return $posts_in;
	}

	/**
	 * Apply the rpt_exclude_post filter.
	 *
	 * @param array<int> $posts_in Related IDs.
	 * @return array<int>
	 * @since 6.0.0
	 */
	private function rpt_apply_exclude_filter( $posts_in ) {
		$exclude_by_filter = apply_filters( 'rpt_exclude_post', '' );

		if ( '' === $exclude_by_filter ) {
			return $posts_in;
		}

		$regex             = '/^\d+(?:,\d+)*$/';
		$exclude_by_filter = preg_match( $regex, $exclude_by_filter ) ? $exclude_by_filter : array();
		$exclude_by_filter_ids = explode( ',', $exclude_by_filter );

		return array_diff( $posts_in, $exclude_by_filter_ids );
	}

	/**
	 * Fetch post rows for the related IDs.
	 *
	 * @param array<int> $posts_in Related IDs.
	 * @param string     $sort_by  ORDER BY fragment.
	 * @param string     $debug    Debug output passed by reference.
	 * @return array<object>
	 * @since 6.0.0
	 */
	private function rpt_fetch_related_posts( $posts_in, $sort_by, &$debug ) {
		global $wpdb;

		$posts_in = array_values(
			array_filter(
				array_map( 'absint', (array) $posts_in )
			)
		);

		if ( empty( $posts_in ) ) {
			return array();
		}

		$placeholders = implode( ', ', array_fill( 0, count( $posts_in ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Placeholders from absint IDs; ORDER BY from rpt_sanitize_related_sort_by() whitelist.
		$posts = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- See ignore above.
			$wpdb->prepare(
				"SELECT ID, post_content, post_excerpt, post_title FROM {$wpdb->posts} WHERE ID IN ($placeholders) ORDER BY {$sort_by}",
				...$posts_in
			)
		);

		if ( ! ( is_array( $posts ) && count( $posts ) > 0 ) ) {
			$debug .= 'No posts found;';

			return array();
		}

		$debug .= 'Found ' . count( $posts ) . ' posts;';

		return $posts;
	}

	/**
	 * Resolve thumbnail width and height.
	 *
	 * @param string $thsource    Thumbnail source.
	 * @param string $poststhname Image size name.
	 * @param string $debug       Debug output passed by reference.
	 * @return array{width: mixed, height: mixed}
	 * @since 6.0.0
	 */
	private function rpt_resolve_thumbnail_sizes( $thsource, $poststhname, &$debug ) {
		$width  = '';
		$height = '';

		if ( 'custom-field' === $thsource ) {
			$debug .= 'Custom sizes;';
			$width  = (string) rpt_settings()->rpt_get( 'thumbnails.custom_width', $this->plugin->custom_width );
			$height = (string) rpt_settings()->rpt_get( 'thumbnails.custom_height', $this->plugin->custom_height );
		} else {
			$subsizes = wp_get_registered_image_subsizes();

			if ( isset( $subsizes[ $poststhname ] ) ) {
				$debug .= 'Registered sizes;';
				$width  = $subsizes[ $poststhname ]['width'];
				$height = $subsizes[ $poststhname ]['height'];
			} else {
				$debug .= 'No registered sizes;';
			}
		}

		if ( 9999 == $height ) {
			$height = $width;
		}

		if ( 9999 == $width ) {
			$width = $height;
		}

		if ( empty( $width ) ) {
			$debug .= 'Using default width;';
			$width  = get_option( 'thumbnail_size_w' );
		}

		if ( empty( $height ) ) {
			$debug .= 'Using default height;';
			$height = get_option( 'thumbnail_size_h' );
		}

		$debug .= 'Got sizes ' . $width . 'x' . $height . ';';

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Append the related posts heading.
	 *
	 * @param string $output    Current HTML.
	 * @param bool   $show_top  Whether to show the heading.
	 * @param string $main_title Optional override title.
	 * @param string $title_tag Heading tag.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_render_heading( $output, $show_top, $main_title, $title_tag ) {
		if ( ! $show_top ) {
			return $output;
		}

		if ( ! empty( $main_title ) ) {
			return $output . '<' . esc_attr( $title_tag ) . ' class="relpoststh-block-title">' . esc_html( $main_title ) . '</' . esc_attr( $title_tag ) . '>';
		}

		$top_text = (string) rpt_settings()->rpt_get( 'general.top_text', '' );

		if ( '' === trim( $top_text ) ) {
			$top_text = wp_strip_all_tags( $this->plugin->top_text );
		}

		return $output . apply_filters(
			'rpt_top_text',
			rpt_settings_format_top_text_html( $top_text, $title_tag )
		);
	}

	/**
	 * Open the list or grid container.
	 *
	 * @param string               $output  Current HTML.
	 * @param array<string, mixed> $context Render context.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_open_items_container( $output, $context ) {
		if ( 'list' === $context['output_style'] ) {
			$output .= '<!-- related_posts_thumbnails -->';
			$output .= '<ul id="related_posts_thumbnails"';

			if ( ! $context['clean_html'] ) {
				$output .= ' style="list-style-type:none; list-style-position: inside; padding: 0; margin:0"';
			}

			$output .= '>';

			return $output;
		}

		$output .= '<div style="clear: both"></div>';
		$output .= '<div style="clear: both"></div>';
		$output .= '<!-- relpost-block-container -->';
		$output .= '<div class="relpost-block-container ' . $context['column_layout'] . '" style="--relposth-columns: ' . $context['column'] . ';--relposth-columns_t: ' . $context['column_t'] . '; --relposth-columns_m: ' . $context['column_m'] . '">';

		return $output;
	}

	/**
	 * Render each related post item.
	 *
	 * @param string               $output  Current HTML.
	 * @param array<object>        $posts   Post rows.
	 * @param array<string, mixed> $sizes   Width/height.
	 * @param array<string, mixed> $context Render context.
	 * @param string               $debug   Debug output passed by reference.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_render_items( $output, $posts, $sizes, $context, &$debug ) {
		foreach ( $posts as $post ) {
			$output .= $this->rpt_render_item( $post, $sizes, $context, $debug );
		}

		return $output;
	}

	/**
	 * Render a single related post item.
	 *
	 * @param object               $post    Post row.
	 * @param array<string, mixed> $sizes   Width/height.
	 * @param array<string, mixed> $context Render context.
	 * @param string               $debug   Debug output passed by reference.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_render_item( $post, $sizes, $context, &$debug ) {
		$post_id    = $post->ID;
		$taxonomies = get_object_taxonomies( $context['post_type'] );
		$image_data = $this->rpt_resolve_image( $post, $sizes, $context, $debug );

		if ( false === $image_data ) {
			return '';
		}

		$title_plain = $this->plugin->process_text_cut( $post->post_title, $context['text_length'] );
		$excerpt     = $this->plugin->process_text_cut(
			( empty( $post->post_excerpt ) ) ? $post->post_content : $post->post_excerpt,
			$context['excerpt_length']
		);
		$alt        = $image_data['alt'];
		$aria_label = 'aria-label="' . esc_attr( $alt ) . '"';

		if ( empty( $alt ) ) {
			$alt        = str_replace( '"', '', $title_plain );
			$aria_label = 'aria-hidden="true"';
		}

		$title = '';

		if ( ! empty( $title_plain ) ) {
			$card_title_tag = 'h3';
			$title          = '<' . $card_title_tag . ' class="relpost_card_title">' . esc_html( $title_plain ) . '</' . $card_title_tag . '>';
		}

		if ( ! empty( $excerpt ) ) {
			$excerpt = '<div class="relpost_card_exerpt">' . esc_html( $excerpt ) . '</div>';
		}

		$debug         .= 'Using title with size ' . $context['text_length'] . '. Using excerpt with size ' . $context['excerpt_length'] . ';';
		$after_content  = apply_filters( 'rpth_after_content', '', $post );
		$date_output    = '';
		$category_list  = '';

		if ( $context['show_date'] ) {
			$date_output = '<span class="rpth_list_date">' . get_the_date( $context['date_format'], $post_id ) . '</span>';
		}

		if ( $context['show_category'] ) {
			$category_list = $this->plugin->rpt_get_post_category_list( $context['show_category'], $taxonomies, $post, $context['post_type'] );
		}

		if ( 'list' === $context['output_style'] ) {
			return $this->rpt_render_list_item( $post_id, $image_data, $sizes, $context, $alt, $title, $excerpt, $date_output, $category_list, $after_content );
		}

		return $this->rpt_render_block_item( $post_id, $image_data, $sizes, $context, $alt, $aria_label, $title, $title_plain, $excerpt, $date_output, $category_list, $after_content );
	}

	/**
	 * Resolve the image URL and alt text for a related post.
	 *
	 * @param object               $post    Post row.
	 * @param array<string, mixed> $sizes   Width/height.
	 * @param array<string, mixed> $context Render context.
	 * @param string               $debug   Debug output passed by reference.
	 * @return array{url: string, alt: string, image: string, attachment_id: int, width: int, height: int}|false
	 * @since 6.0.0
	 */
	private function rpt_resolve_image( $post, $sizes, $context, &$debug ) { // NOSONAR — if statements
		$url           = '';
		$alt           = '';
		$image         = '';
		$attachment_id = 0;
		$width         = 0;
		$height        = 0;

		if ( 'custom-field' === $context['thsource'] ) {
			$url = $this->rpt_resolve_custom_field_image( $post->ID, $sizes, $debug );

			if ( false === $url ) {
				return false;
			}
		} elseif ( current_theme_supports( 'post-thumbnails' ) ) {
			$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
			$debug            .= 'Post-thumbnails enabled in theme;';

			if ( ! ( empty( $post_thumbnail_id ) || false === $post_thumbnail_id ) ) {
				$attachment_id = absint( $post_thumbnail_id );
				$debug        .= 'Post has thumbnail ' . $attachment_id . ';';
				$debug        .= 'Postthname: ' . $context['poststhname'] . ';';
				$image         = wp_get_attachment_image_src( $attachment_id, $context['poststhname'] );
				$alt           = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
				$url           = ! is_bool( $image ) && isset( $image[0] ) ? $image[0] : false;

				if ( is_array( $image ) ) {
					$width  = absint( $image[1] ?? 0 );
					$height = absint( $image[2] ?? 0 );
				}
			} else {
				$debug .= 'Post has no thumbnail;';
			}
		}

		if ( is_string( $url ) && strpos( $url, '/' ) === 0 ) {
			$url    = get_bloginfo( 'url' ) . $url;
			$debug .= 'Relative url: ' . $url . ';';
		}

		if ( empty( $url ) && $context['first_image'] ) {
			$body_image = $this->rpt_resolve_first_body_image( $post, $sizes, $debug );
			$url        = $body_image['url'];
			$alt        = '' !== $body_image['alt'] ? $body_image['alt'] : $alt;
			$image      = $body_image['image'];
		}

		if ( empty( $url ) ) {
			$debug .= 'Image URL is empty; Using default image;';
			$url    = $this->plugin->rpt_get_default_image_url();
		}

		return array(
			'url'           => $url,
			'alt'           => $alt,
			'image'         => $image,
			'attachment_id' => $attachment_id,
			'width'         => $width,
			'height'        => $height,
		);
	}

	/**
	 * Resolve a custom-field image URL.
	 *
	 * @param int                  $post_id Post ID.
	 * @param array<string, mixed> $sizes   Width/height.
	 * @param string               $debug   Debug output passed by reference.
	 * @return string|false URL, or false to skip the post.
	 * @since 6.0.0
	 */
	private function rpt_resolve_custom_field_image( $post_id, $sizes, &$debug ) {
		$custom_field      = (string) rpt_settings()->rpt_get( 'thumbnails.customfield', $this->plugin->custom_field );
		$custom_field_meta = get_post_meta( $post_id, $custom_field );

		if ( empty( $custom_field ) ) {
			$debug .= 'No custom field specifield, using default thumbnail image;';

			return $this->plugin->rpt_get_default_image_url();
		}

		if ( empty( $custom_field_meta ) && apply_filters( 'rpt_remove_empty_cfield', false ) ) {
			$debug .= 'Custom field meta is empty, using rpt_remove_empty_cfield filter;';

			return false;
		}

		if ( empty( $custom_field_meta ) ) {
			$debug .= 'Custom field meta is empty, using default thumbnail image;';

			return $this->plugin->rpt_get_default_image_url();
		}

		$debug           .= 'Using custom field;';
		$customfield_type = (string) rpt_settings()->rpt_get( 'thumbnails.image_type', $this->plugin->customfield_type );

		if ( ! in_array( $customfield_type, array( 'image', 'url' ), true ) ) {
			$customfield_type = 'image';
		}

		if ( is_array( $custom_field_meta ) && isset( $custom_field_meta[0] ) ) {
			$meta_value = $custom_field_meta[0];

			if ( 'url' === $customfield_type ) {
				$url = ! empty( $meta_value ) ? $meta_value : $this->plugin->rpt_get_default_image_url();
			} elseif ( is_numeric( $meta_value ) && (int) $meta_value ) {
				$attachment = wp_get_attachment_image_src( (int) $meta_value );
				$url        = $attachment ? $attachment[0] : $this->plugin->rpt_get_default_image_url();
			} else {
				$url = ! empty( $meta_value ) ? $meta_value : $this->plugin->rpt_get_default_image_url();
			}
		} else {
			$url = isset( $custom_field_meta ) && ! empty( $custom_field_meta ) ? $custom_field_meta : $this->plugin->rpt_get_default_image_url();
		}

		$theme_resize_url = (string) rpt_settings()->rpt_get( 'thumbnails.theme_resize_url', '' );

		if ( is_string( $url ) && false !== strpos( $url, '/wp-content' ) ) {
			$url = substr( $url, strpos( $url, '/wp-content' ) );
		}

		if ( ! empty( $theme_resize_url ) ) {
			$url = $theme_resize_url . '?src=' . $url . '&w=' . $sizes['width'] . '&h=' . $sizes['height'] . '&zc=1&q=90';
		}

		return $url;
	}

	/**
	 * Pull the first uploaded image from post content.
	 *
	 * @param object               $post  Post row.
	 * @param array<string, mixed> $sizes Width/height.
	 * @param string               $debug Debug output passed by reference.
	 * @return array{url: string, alt: string, image: string}
	 * @since 6.0.0
	 */
	private function rpt_resolve_first_body_image( $post, $sizes, &$debug ) {
		$debug .= 'Getting image from post body;';
		$wud    = wp_upload_dir();
		$url    = '';
		$alt    = '';
		$image  = '';

		preg_match_all( '|<img.*?src=[\'"](' . $wud['baseurl'] . '.*?)[\'"].*?>|i', $post->post_content, $matches );

		if ( isset( $matches ) && isset( $matches[1][0] ) ) {
			$image = $matches[1][0];
			$html  = $matches[0][0];

			if ( ! empty( $html ) ) {
				preg_match( '/alt="([^"]*)"/i', $html, $array );

				if ( ! empty( $array ) && is_array( $array ) ) {
					$explode_tag = explode( '"', $array[0] );
					$alt         = $explode_tag[1];
				}
			}
		} else {
			$debug .= 'No image was found;';
		}

		if ( strlen( trim( $image ) ) > 0 ) {
			$image_sizes = @getimagesize( $image );

			if ( false === $image_sizes ) {
				$debug .= 'Unable to determine parsed image size';
			}

			if ( ( false !== $image_sizes && isset( $image_sizes[0] ) ) && $image_sizes[0] == $sizes['width'] ) {
				$debug .= 'Image used is the required size;';
				$url    = $image;
			} elseif ( apply_filters( 'rpt_prevent_img_size_check', false ) && $image_sizes[0] < $sizes['width'] ) {
				$debug .= 'Image used is smaller than the required size, rpt_prevent_img_size_check filter is active;';
				$url    = $image;
			} else {
				$debug .= 'Changing image according to Wordpress standards;';
				$url    = $image;
			}
		} else {
			$debug .= '' !== (string) $image
				? 'Found wrong formatted image: ' . $image . ';'
				: 'Found wrong formatted image;';
		}

		return array(
			'url'   => $url,
			'alt'   => $alt,
			'image' => $image,
		);
	}

	/**
	 * Resolve display width/height for an image, preferring the real file dimensions.
	 *
	 * @param array<string, mixed> $image_data Image payload.
	 * @param array<string, mixed> $sizes      Fallback layout sizes.
	 * @return array{width: mixed, height: mixed}
	 * @since 6.0.0
	 */
	private function rpt_get_image_display_sizes( $image_data, $sizes ) {
		$width  = absint( $image_data['width'] ?? 0 );
		$height = absint( $image_data['height'] ?? 0 );

		if ( $width > 0 && $height > 0 ) {
			return array(
				'width'  => $width,
				'height' => $height,
			);
		}

		return array(
			'width'  => $sizes['width'] ?? '',
			'height' => $sizes['height'] ?? '',
		);
	}

	/**
	 * Build optional srcset/sizes attributes for an attachment image.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $size_name     Registered image size name.
	 * @return string HTML attributes (leading space when present).
	 * @since 6.0.0
	 */
	private function rpt_get_image_srcset_attributes( $attachment_id, $size_name ) {
		$attachment_id = absint( $attachment_id );

		if ( $attachment_id <= 0 ) {
			return '';
		}

		$srcset = wp_get_attachment_image_srcset( $attachment_id, $size_name );
		$sizes  = wp_get_attachment_image_sizes( $attachment_id, $size_name );
		$attrs  = '';

		if ( $srcset ) {
			$attrs .= ' srcset="' . esc_attr( $srcset ) . '"';
		}

		if ( $sizes ) {
			$attrs .= ' sizes="' . esc_attr( $sizes ) . '"';
		}

		return $attrs;
	}

	/**
	 * Render a list-style item.
	 *
	 * @param int                  $post_id        Post ID.
	 * @param array<string, mixed> $image_data     Image URL/alt/attachment data.
	 * @param array<string, mixed> $sizes          Width/height.
	 * @param array<string, mixed> $context        Render context.
	 * @param string               $alt            Alt text.
	 * @param string               $title          Title HTML.
	 * @param string               $excerpt        Excerpt HTML.
	 * @param string               $date_output    Date HTML.
	 * @param string               $category_list  Category HTML.
	 * @param string               $after_content  After-content HTML.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_render_list_item( $post_id, $image_data, $sizes, $context, $alt, $title, $excerpt, $date_output, $category_list, $after_content ) {
		$link          = esc_url( get_permalink( $post_id ) );
		$display_sizes = $this->rpt_get_image_display_sizes( $image_data, $sizes );
		$srcset        = $this->rpt_get_image_srcset_attributes(
			(int) ( $image_data['attachment_id'] ?? 0 ),
			(string) ( $context['poststhname'] ?? 'thumbnail' )
		);
		$output = '<ul><li>';
		$output .= '<a href="' . $link . '"><img class="relpost-post-image" loading="lazy" decoding="async" alt="' . esc_attr( $alt ) . '" src="' . esc_url( $image_data['url'] ) . '" width="' . esc_attr( $display_sizes['width'] ) . '" height="' . esc_attr( $display_sizes['height'] ) . '"' . $srcset . ' ';

		if ( $context['image_size'] ) {
			$output .= 'style="aspect-ratio:' . esc_attr( $context['image_size'] ) . '"';
		}

		$output .= '/></a>';

		if ( '0' !== $context['text_height'] ) {
			$output .= '<a href="' . $link . '" class="relpost_content"';

			if ( ! $context['clean_html'] ) {
				$output .= ' style="width: ' . esc_attr( $display_sizes['width'] ) . 'px;height: ' . esc_attr( $context['text_height'] ) . 'px; font-family: ' . esc_attr( $context['font_family'] ) . ';"';
			}

			$output .= '><span class="rpth_list_content">' . $title . $excerpt . '</span>' . $date_output . $category_list . $after_content . '</a>';
		}

		$output .= '</li></ul>';

		return $output;
	}

	/**
	 * Render a block-style item.
	 *
	 * @param int                  $post_id        Post ID.
	 * @param array<string, mixed> $image_data     Image URL/alt/attachment data.
	 * @param array<string, mixed> $sizes          Width/height.
	 * @param array<string, mixed> $context        Render context.
	 * @param string               $alt            Alt text.
	 * @param string               $aria_label     ARIA attribute HTML.
	 * @param string               $title          Title HTML.
	 * @param string               $title_plain    Plain title text for the card link aria-label.
	 * @param string               $excerpt        Excerpt HTML.
	 * @param string               $date_output    Date HTML.
	 * @param string               $category_list  Category HTML.
	 * @param string               $after_content  After-content HTML.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_render_block_item( $post_id, $image_data, $sizes, $context, $alt, $aria_label, $title, $title_plain, $excerpt, $date_output, $category_list, $after_content ) {
		$relpost_attributes = $this->rpt_get_block_anchor_attributes( $post_id );
		$link_label         = '' !== trim( (string) $title_plain )
			? trim( (string) $title_plain )
			: get_the_title( $post_id );

		$output  = '<a href="' . esc_url( get_permalink( $post_id ) ) . '"' . $relpost_attributes . ' aria-label="' . esc_attr( $link_label ) . '">';
		$output .= '<div class="relpost-custom-block-single">';
		$output .= $this->rpt_render_block_thumbnail( $image_data, $sizes, $context, $alt, $aria_label );
		$output .= '<div class="relpost-block-single-text"  style="height: ' . esc_attr( $context['text_height'] ) . 'px;font-family: ' . esc_attr( $context['font_family'] ) . ';  font-size: ' . esc_attr( $context['font_size'] ) . 'px;  color: ' . esc_attr( $context['font_color'] ) . ';">' . $title . $excerpt . '</div>';
		$output .= $date_output . $category_list;
		$output .= $after_content;
		$output .= '</div>';
		$output .= '</a>';

		return $output;
	}

	/**
	 * Render a block-style thumbnail using a semantic image by default.
	 *
	 * @param array<string, mixed> $image_data  Image URL/alt/attachment data.
	 * @param array<string, mixed> $sizes       Width/height.
	 * @param array<string, mixed> $context     Render context.
	 * @param string               $alt         Alt text.
	 * @param string               $aria_label  ARIA attribute HTML.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_render_block_thumbnail( $image_data, $sizes, $context, $alt, $aria_label ) {
		$url           = (string) ( $image_data['url'] ?? '' );
		$display_sizes = $this->rpt_get_image_display_sizes( $image_data, $sizes );

		if ( apply_filters( 'rpt_use_background_thumbnail', false ) ) {
			$rpt_single_background = apply_filters(
				'rpt-single-background',
				'data-bg="' . esc_url( $url ) . '" style="background: transparent no-repeat scroll 0% 0%; width: ' . esc_attr( $display_sizes['width'] ) . 'px; height: ' . esc_attr( $display_sizes['height'] ) . 'px; aspect-ratio: ' . esc_attr( $context['image_size'] ) . ';"'
			);

			return '<div class="relpost-block-single-image rpt-lazyload" ' . $aria_label . ' ' . $rpt_single_background . '></div>';
		}

		$srcset = $this->rpt_get_image_srcset_attributes(
			(int) ( $image_data['attachment_id'] ?? 0 ),
			(string) ( $context['poststhname'] ?? 'thumbnail' )
		);

		return '<img loading="lazy" decoding="async" class="relpost-block-single-image" alt="' . esc_attr( $alt ) . '" src="' . esc_url( $url ) . '" width="' . esc_attr( $display_sizes['width'] ) . '" height="' . esc_attr( $display_sizes['height'] ) . '"' . $srcset . ' style="aspect-ratio:' . esc_attr( $context['image_size'] ) . ';object-fit:cover;" />';
	}

	/**
	 * Build sanitized anchor attributes for block items.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_get_block_anchor_attributes( $post_id ) {
		$rpt_anchor_attrs = array(
			'class'  => '',
			'target' => false,
		);
		$rpt_anchor_attr_filter = (array) apply_filters( 'relpost_anchor_attr', $post_id, $rpt_anchor_attrs );
		$allowed_anchor_attrs   = array( 'title', 'class', 'target' );
		$data_attr_ptrn         = '/data-/i';
		$relpost_attributes     = 'class="relpost-block-single';

		if ( array_key_exists( 'class', $rpt_anchor_attr_filter ) ) {
			$class_attrs_str = esc_attr( wp_unslash( $rpt_anchor_attr_filter['class'] ) );
			$class_attrs     = explode( ' ', $class_attrs_str );

			foreach ( $class_attrs as $value ) {
				if ( 'relpost-block-single' !== $value && ! empty( $value ) ) {
					$relpost_attributes .= ' ' . $value;
				}
			}
		}

		$relpost_attributes .= '" ';

		foreach ( $rpt_anchor_attr_filter as $rel_post_a_attr => $value ) {
			$value             = esc_attr( wp_unslash( $value ) );
			$rel_post_a_attr   = esc_attr( wp_unslash( $rel_post_a_attr ) );

			if ( false !== $value && ! empty( $value ) && 'class' !== $rel_post_a_attr && ( in_array( $rel_post_a_attr, $allowed_anchor_attrs, true ) || preg_match( $data_attr_ptrn, $rel_post_a_attr ) ) ) {
				$relpost_attributes .= $rel_post_a_attr . '="' . $value . '"';
			}
		}

		return $relpost_attributes;
	}

	/**
	 * Close list/grid wrappers.
	 *
	 * @param string $output       Current HTML.
	 * @param string $output_style list or block.
	 * @return string
	 * @since 6.0.0
	 */
	private function rpt_close_markup( $output, $output_style ) {
		if ( 'list' === $output_style ) {
			$output .= '</ul>';
			$output .= '<!-- close related_posts_thumbnails -->';
		} else {
			$output .= '</div>';
			$output .= '<!-- close relpost-block-container -->';
		}

		$output .= '<div style="clear: both"></div>';
		$output .= '</div>';
		$output .= '<!-- close filter class -->';
		$output .= '</div>';
		$output .= '<!-- close relpost-thumb-wrapper -->';

		return $output;
	}
}
