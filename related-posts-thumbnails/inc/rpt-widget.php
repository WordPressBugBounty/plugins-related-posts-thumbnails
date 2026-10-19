<?php
/**
 * Related Posts Thumbnails widget.
 *
 * @package RelatedPostsThumbnails
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Related posts Thumbnails function to get the thumbnails
 *
 * @return void
 */
function get_related_posts_thumbnails() {
	global $related_posts_thumbnails;

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Intentional frontend HTML from the plugin renderer.
	echo $related_posts_thumbnails->get_html();
}

/**
 * Related Posts Widget, will be displayed on post page
 */
class RelatedPostsThumbnailsWidget extends WP_Widget { // NOSONAR — WPCS Class_Name (php:S101)

	/**
	 * Constructor.
	 */
	public function __construct() {
		$args = array(
			'classname' => 'relpoststh_widget',
		);
		parent::__construct( false, 'Related Posts Thumbnails', $args );
	}

	/**
	 * Show Widget
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 * @return void
	 * @version 6.0.0
	 */
	public function widget( $args, $instance ) {
		if ( is_single() && ! is_page() ) { // display on post page only
			$title = apply_filters( 'widget_title', $instance['title'] ?? '' );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-supplied widget wrapper markup.
			echo $args['before_widget'];

			if ( $title ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-supplied widget title wrappers.
				echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
			}

			get_related_posts_thumbnails();

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Theme-supplied widget wrapper markup.
			echo $args['after_widget'];
		}
	}

	/**
	 * Widget Instance update
	 *
	 * @param array $new_instance New instance.
	 * @param array $old_instance Old instance.
	 * @return array
	 * @version 6.0.0
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$instance['title'] = wp_strip_all_tags( $new_instance['title'] ?? '' );

		return $instance;
	}

	/**
	 * Widget Form
	 *
	 * @param array $instance Widget instance.
	 * @return void
	 * @version 6.0.0
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'related-posts-thumbnails' ); ?>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
			</label>
		</p>
		<?php
	}
}

/**
 * Register Widget.
 *
 * @return void
 */
function register_wpb_rpt_widget() {
	register_widget( 'RelatedPostsThumbnailsWidget' );
}
add_action( 'widgets_init', 'register_wpb_rpt_widget' );
