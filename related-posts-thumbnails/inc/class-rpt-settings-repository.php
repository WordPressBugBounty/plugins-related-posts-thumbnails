<?php
/**
 * Read/write access for tab-based plugin settings.
 *
 * @package RelatedPostsThumbnails
 * @since   6.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings repository singleton.
 *
 * @since 6.0.0
 */
class RPT_Settings_Repository {

	/**
	 * Singleton repository instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * In-request cache of merged tab settings.
	 *
	 * @var array<string, mixed>|null
	 */
	private $cache = null;

	/**
	 * Migration helper used for merge and one-time storage upgrade.
	 *
	 * @var RPT_Settings_Migration
	 */
	private $migration;

	/**
	 * Private constructor; use rpt_instance().
	 *
	 * @param RPT_Settings_Migration $migration Migration helper.
	 */
	private function __construct( RPT_Settings_Migration $migration ) {
		$this->migration = $migration;
	}

	/**
	 * Get or create the settings repository singleton.
	 *
	 * @param RelatedPostsThumbnails|null $plugin Plugin instance.
	 * @return self
	 * @since 6.0.0
	 */
	public static function rpt_instance( $plugin = null ) {
		if ( null === self::$instance ) {
			self::$instance = new self( new RPT_Settings_Migration( $plugin ) );
		}

		return self::$instance;
	}

	/**
	 * Run storage boot (fresh init or deferred legacy upgrade).
	 *
	 * @param RelatedPostsThumbnails|null $plugin Plugin instance.
	 * @return void
	 * @since 6.0.0
	 */
	public static function rpt_boot( $plugin = null ) {
		$repo = self::rpt_instance( $plugin );
		$repo->migration->rpt_ensure_storage();
	}

	/**
	 * Whether admin consent is required before persisting the new storage format.
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public function rpt_needs_migration_consent() {
		return $this->migration->rpt_needs_consent();
	}

	/**
	 * Run the legacy-to-tab storage migration after admin consent.
	 *
	 * @return bool True on success.
	 * @since 6.0.0
	 */
	public function rpt_run_migration() {
		$success = $this->migration->rpt_run_migration();
		$this->rpt_flush_cache();

		return $success;
	}

	/**
	 * Clear the in-request settings cache so the next read reloads from wp_options.
	 *
	 * Does not purge related post ID cache; that is handled separately on save.
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function rpt_flush_cache() {
		$this->cache = null;
	}

	/**
	 * Get all tab settings merged with defaults.
	 *
	 * @return array<string, array<string, mixed>>
	 * @since 6.0.0
	 */
	public function rpt_all() {
		if ( null !== $this->cache ) {
			return $this->cache;
		}

		if ( $this->migration->rpt_needs_consent() ) {
			$this->cache = $this->migration->rpt_build_from_legacy();

			return $this->cache;
		}

		$merged = array();

		foreach ( RPT_Settings_Schema::rpt_get_tabs() as $tab ) {
			$stored = get_option( RPT_Settings_Schema::rpt_get_tab_option_name( $tab ), array() );
			$merged[ $tab ] = $this->migration->rpt_merge_tab_with_defaults(
				$tab,
				is_array( $stored ) ? $stored : array()
			);
		}

		$this->cache = $merged;

		return $this->cache;
	}

	/**
	 * Get a nested value using dot notation (e.g. general.auto).
	 *
	 * @param string $path    Dot-separated path.
	 * @param mixed  $default Fallback when missing.
	 * @return mixed
	 * @since 6.0.0
	 */
	public function rpt_get( $path, $default = null ) {
		$segments = explode( '.', $path );
		$value    = $this->rpt_all();

		foreach ( $segments as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $default;
			}

			$value = $value[ $segment ];
		}

		return $value;
	}

	/**
	 * Persist all tab settings.
	 *
	 * @param array<string, array<string, mixed>> $settings Settings keyed by tab.
	 * @return bool
	 * @since 6.0.0
	 */
	public function rpt_save( array $settings ) {
		if ( $this->rpt_needs_migration_consent() ) {
			return false;
		}

		$settings = $this->migration->rpt_merge_with_defaults( $settings );
		$this->migration->rpt_write_tab_options( $settings );
		$this->rpt_flush_cache();

		return true;
	}

	/**
	 * Save one tab without touching the others.
	 *
	 * @param string               $tab    Tab slug.
	 * @param array<string, mixed> $values Tab values.
	 * @return bool
	 * @since 6.0.0
	 */
	public function rpt_save_tab( $tab, array $values ) {
		if ( $this->rpt_needs_migration_consent() ) {
			return false;
		}

		if ( ! in_array( $tab, RPT_Settings_Schema::rpt_get_tabs(), true ) ) {
			return false;
		}

		update_option(
			RPT_Settings_Schema::rpt_get_tab_option_name( $tab ),
			$this->migration->rpt_merge_tab_with_defaults( $tab, $values ),
			RPT_Settings_Schema::rpt_tab_autoload()
		);

		$this->rpt_flush_cache();

		return true;
	}
}

if ( ! function_exists( 'rpt_settings' ) ) {
	/**
	 * Access plugin settings repository.
	 *
	 * @return RPT_Settings_Repository
	 * @since  6.0.0
	 */
	function rpt_settings() {
		global $related_posts_thumbnails;

		return RPT_Settings_Repository::rpt_instance(
			isset( $related_posts_thumbnails ) ? $related_posts_thumbnails : null
		);
	}
}
