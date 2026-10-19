<?php
/**
 * REST API for Related Posts Thumbnails settings.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and handles RPT REST routes.
 *
 * @since 6.0.0
 */
class RPT_REST_API {

	/**
	 * API namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'rpt/v1';

	/**
	 * Settings service.
	 *
	 * @var RPT_Settings_Service
	 */
	private $settings_service;

	/**
	 * Constructor.
	 *
	 * @param RPT_Settings_Service $settings_service Settings service.
	 * @since 6.0.0
	 */
	public function __construct( $settings_service ) {
		$this->settings_service = $settings_service;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'can_manage_settings' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'can_manage_settings' ),
				),
			)
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/settings/migrate',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'rpt_migrate_settings' ),
				'permission_callback' => array( $this, 'can_manage_settings' ),
			)
		);

	}

	/**
	 * Permission check for settings endpoints.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public function can_manage_settings() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * GET /settings — return settings and UI meta for the admin app.
	 *
	 * @return WP_REST_Response
	 * @since 6.0.0
	 */
	public function get_settings() {
		return rest_ensure_response(
			array(
				'settings' => $this->settings_service->rpt_get_settings(),
				'meta'     => $this->settings_service->rpt_get_meta(),
			)
		);
	}

	/**
	 * PUT /settings — persist settings from the admin app payload.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error
	 * @since 6.0.0
	 */
	public function update_settings( $request ) {
		$payload = $request->get_json_params();
		$result  = $this->settings_service->rpt_update_settings( $payload );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * POST /settings/migrate — upgrade legacy flat options to tab storage.
	 *
	 * @return WP_REST_Response|WP_Error
	 * @since 6.0.0
	 */
	public function rpt_migrate_settings() {
		$result = $this->settings_service->rpt_run_settings_migration();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

}
