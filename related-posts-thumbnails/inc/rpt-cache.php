<?php
/**
 * Related post IDs cache helpers.
 *
 * @package RelatedPostsThumbnails
 * @since 5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta key that stores related post ID cache entries for a single post.
 * Value is an array keyed by context hash; each entry has ids, expires, version.
 */
define( 'RPT_RELATED_CACHE_META_KEY', '_rpt_related_ids_cache' );

/**
 * Site option holding the global cache version number.
 * Bumping this instantly invalidates all existing entries without deleting meta.
 */
define( 'RPT_CACHE_VERSION_OPTION', 'relpoststh_cache_version' );

/**
 * Site option for the Settings checkbox "Cache related post IDs" (1 = on, 0 = off).
 */
define( 'RPT_CACHE_ENABLED_OPTION', 'relpoststh_cache_enabled' );

/**
 * Flag option set when a site-wide purge schedules leftover meta cleanup.
 * Cleared after batch deletion removes all `_rpt_related_ids_cache` rows.
 */
define( 'RPT_CACHE_PURGE_PENDING_OPTION', 'relpoststh_cache_purge_pending' );

/**
 * Default number of cache meta rows deleted per admin request during batch cleanup.
 * Override via the `rpt_related_cache_batch_size` filter.
 */
define( 'RPT_CACHE_BATCH_SIZE', 500 );

/**
 * Transient key for the cached "site has multiple publishers" check (author-related feature).
 */
define( 'RPT_MULTIPLE_PUBLISHERS_TRANSIENT', 'rpt_has_multiple_publishers' );

/**
 * Whether related post ID caching is enabled.
 *
 * Reads the Settings checkbox, then applies `rpt_related_ids_cache_enabled`.
 * When false: no read/write of post meta cache; admin bar purge link is hidden.
 *
 * Filter: rpt_related_ids_cache_enabled
 * - Arg 1 (bool): Whether caching is enabled.
 * - Return bool.
 *
 * Example (force enable regardless of setting):
 * add_filter( 'rpt_related_ids_cache_enabled', '__return_true' );
 *
 * Example (force disable):
 * add_filter( 'rpt_related_ids_cache_enabled', '__return_false' );
 *
 * @return bool
 * @since 5.0.0
 */
function rpt_is_related_cache_enabled() {
	$enabled = get_option( RPT_CACHE_ENABLED_OPTION, '1' );

	return (bool) apply_filters( 'rpt_related_ids_cache_enabled', '1' === $enabled );
}

/**
 * Cache time-to-live (TTL) in seconds.
 *
 * Default is 24 hours (`DAY_IN_SECONDS`). Stored on each entry as `expires`.
 * After expiry, that entry is treated as a miss and related IDs are queried again.
 *
 * Filter: rpt_related_ids_cache_ttl
 * - Arg 1 (int): TTL in seconds (default `DAY_IN_SECONDS`).
 * - Return int (seconds). Use a positive value.
 *
 * Example (cache for 12 hours):
 * add_filter( 'rpt_related_ids_cache_ttl', function () {
 *     return 12 * HOUR_IN_SECONDS;
 * } );
 *
 * Example (cache for 1 hour):
 * add_filter( 'rpt_related_ids_cache_ttl', function () {
 *     return HOUR_IN_SECONDS;
 * } );
 *
 * @return int
 * @since 5.0.0
 */
function rpt_get_related_cache_ttl() {
	return (int) apply_filters( 'rpt_related_ids_cache_ttl', DAY_IN_SECONDS );
}

/**
 * Current global cache version.
 *
 * @return int
 * @since 5.0.0
 */
function rpt_get_cache_version() {
	return absint( get_option( RPT_CACHE_VERSION_OPTION, 1 ) );
}

/**
 * Bump the global cache version.
 *
 * @return int New version number.
 * @since 5.0.0
 */
function rpt_bump_cache_version() {
	$new_version = rpt_get_cache_version() + 1;
	update_option( RPT_CACHE_VERSION_OPTION, $new_version );

	return $new_version;
}

/**
 * Query-affecting settings used for cache invalidation comparison.
 *
 * @return array<string, mixed>
 * @since 5.0.0
 */
function rpt_get_query_affecting_settings() {
	return array(
		'relation'           => get_option( 'relpoststh_relation', 'categories' ),
		'show_categoriesall' => get_option( 'relpoststh_show_categoriesall', get_option( 'relpoststh_categoriesall', '1' ) ),
		'categoriesall'      => get_option( 'relpoststh_categoriesall', '1' ),
		'show_categories'    => get_option( 'relpoststh_show_categories', get_option( 'relpoststh_categories', array() ) ),
		'categories'         => get_option( 'relpoststh_categories', array() ),
		'startdate'          => get_option( 'relpoststh_startdate', '' ),
		'custom_taxonomies'  => get_option( 'relpoststh_custom_taxonomies', array() ),
		'post_sort'          => get_option( 'rpt_post_sort', 'rand' ),
		'number'             => absint( get_option( 'relpoststh_number', 3 ) ),
		'author_related'     => get_option( 'relpoststh_author_related', '0' ),
	);
}

/**
 * Whether query-affecting settings changed.
 *
 * @param array<string, mixed> $old_settings Previous settings snapshot.
 * @param array<string, mixed> $new_settings Current settings snapshot.
 * @return bool
 * @since 5.0.0
 */
function rpt_query_settings_changed( $old_settings, $new_settings ) {
	return $old_settings !== $new_settings;
}

/**
 * Settings fingerprint for cache context hashing.
 *
 * @return string
 * @since 5.0.0
 */
function rpt_get_related_cache_settings_fingerprint() {
	$settings = rpt_get_query_affecting_settings();

	$settings['onlywiththumbs'] = get_option( 'relpoststh_onlywiththumbs', '0' );

	return md5( wp_json_encode( $settings ) );
}

/**
 * Build a stable cache context hash for a request.
 *
 * @param array<string, mixed> $args Request arguments.
 * @return string
 * @since 5.0.0
 */
function rpt_build_related_cache_context( $args ) {
	$defaults = array(
		'posts_number' => 0,
		'sort_by'      => '',
		'exclude'      => '',
		'author_id'    => 0,
	);

	$args = wp_parse_args( $args, $defaults );

	if ( is_array( $args['exclude'] ) ) {
		$exclude = array_map( 'absint', $args['exclude'] );
		sort( $exclude );
		$args['exclude'] = implode( ',', $exclude );
	} else {
		$args['exclude'] = (string) $args['exclude'];
	}

	$payload = array(
		'settings'     => rpt_get_related_cache_settings_fingerprint(),
		'posts_number' => absint( $args['posts_number'] ),
		'sort_by'      => (string) $args['sort_by'],
		'exclude'      => $args['exclude'],
		'author_id'    => absint( $args['author_id'] ),
	);

	return md5( wp_json_encode( $payload ) );
}

/**
 * Get cached related post IDs for a post and context.
 *
 * @param int    $post_id Source post ID.
 * @param string $context Cache context hash.
 * @return array<int>|null Cached IDs or null on miss.
 * @since 5.0.0
 */
function rpt_get_cached_related_ids( $post_id, $context ) {
	$post_id = absint( $post_id );

	if ( $post_id <= 0 || empty( $context ) ) {
		return null;
	}

	$cache_data = get_post_meta( $post_id, RPT_RELATED_CACHE_META_KEY, true );

	if ( ! is_array( $cache_data ) || ! isset( $cache_data[ $context ] ) ) {
		return null;
	}

	$entry = $cache_data[ $context ];

	if (
		! is_array( $entry )
		|| empty( $entry['ids'] )
		|| ! is_array( $entry['ids'] )
		|| empty( $entry['expires'] )
		|| empty( $entry['version'] )
	) {
		return null;
	}

	if ( (int) $entry['version'] !== rpt_get_cache_version() ) {
		return null;
	}

	if ( time() > (int) $entry['expires'] ) {
		return null;
	}

	return array_map( 'absint', $entry['ids'] );
}

/**
 * Store related post IDs in cache.
 *
 * @param int          $post_id Source post ID.
 * @param string       $context Cache context hash.
 * @param array<int>   $ids     Related post IDs.
 * @return bool
 * @since 5.0.0
 */
function rpt_set_cached_related_ids( $post_id, $context, $ids ) {
	$post_id = absint( $post_id );

	if ( $post_id <= 0 || empty( $context ) || ! is_array( $ids ) || empty( $ids ) ) {
		return false;
	}

	$ids = array_values( array_unique( array_map( 'absint', $ids ) ) );

	if ( empty( $ids ) ) {
		return false;
	}

	$cache_data = get_post_meta( $post_id, RPT_RELATED_CACHE_META_KEY, true );

	if ( ! is_array( $cache_data ) ) {
		$cache_data = array();
	}

	$cache_data[ $context ] = array(
		'ids'     => $ids,
		'expires' => time() + rpt_get_related_cache_ttl(),
		'version' => rpt_get_cache_version(),
	);

	update_post_meta( $post_id, RPT_RELATED_CACHE_META_KEY, $cache_data );

	return true;
}

/**
 * Validate cached IDs and decide whether a re-query is needed.
 *
 * @param array<int> $ids            Cached post IDs.
 * @param int        $required_count Number of posts required.
 * @param bool       $onlywiththumbs Whether only posts with thumbnails are allowed.
 * @return array{ids: array<int>, requery: bool}
 * @since 5.0.0
 */
function rpt_validate_cached_related_ids( $ids, $required_count, $onlywiththumbs ) {
	$valid_ids = array();

	if ( ! is_array( $ids ) ) {
		return array(
			'ids'     => array(),
			'requery' => true,
		);
	}

	foreach ( $ids as $id ) {
		$id = absint( $id );

		if ( $id <= 0 ) {
			continue;
		}

		if ( 'publish' !== get_post_status( $id ) ) {
			continue;
		}

		if ( $onlywiththumbs && ! get_post_thumbnail_id( $id ) ) {
			continue;
		}

		$valid_ids[] = $id;
	}

	return array(
		'ids'     => $valid_ids,
		'requery' => count( $valid_ids ) < absint( $required_count ),
	);
}

/**
 * Delete all cached related IDs for a post.
 *
 * @param int $post_id Source post ID.
 * @return bool
 * @since 5.0.0
 */
function rpt_delete_post_related_cache( $post_id ) {
	$post_id = absint( $post_id );

	if ( $post_id <= 0 ) {
		return false;
	}

	return (bool) delete_post_meta( $post_id, RPT_RELATED_CACHE_META_KEY );
}

/**
 * Purge all related post caches site-wide.
 *
 * @param bool $batch Whether to schedule batch meta cleanup.
 * @return void
 * @since 5.0.0
 */
function rpt_purge_all_related_cache( $batch = true ) {
	rpt_bump_cache_version();

	if ( $batch ) {
		update_option( RPT_CACHE_PURGE_PENDING_OPTION, '1' );
	}
}

/**
 * Delete cached post meta in batches for large sites.
 *
 * Runs on `admin_init` while the purge-pending flag is set. Deletes up to
 * `RPT_CACHE_BATCH_SIZE` rows per request so large sites stay responsive.
 * Instant invalidation already happened via version bump; this only frees DB space.
 *
 * Filter: rpt_related_cache_batch_size
 * - Arg 1 (int): Rows to delete per admin request (default 500).
 * - Return int. Values below 1 are raised to 1.
 *
 * Example (smaller batches on shared hosting):
 * add_filter( 'rpt_related_cache_batch_size', function () {
 *     return 100;
 * } );
 *
 * Example (larger batches on powerful servers):
 * add_filter( 'rpt_related_cache_batch_size', function () {
 *     return 1000;
 * } );
 *
 * @return bool True when cleanup is complete.
 * @since 5.0.0
 */
function rpt_batch_delete_cache_meta() {
	if ( '1' !== get_option( RPT_CACHE_PURGE_PENDING_OPTION, '0' ) ) {
		return true;
	}

	if ( ! is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	global $wpdb;

	/**
	 * Number of `_rpt_related_ids_cache` meta rows to delete per admin request.
	 *
	 * @since 5.0.0
	 * @param int $batch_size Default `RPT_CACHE_BATCH_SIZE` (500).
	 */
	$batch_size = (int) apply_filters( 'rpt_related_cache_batch_size', RPT_CACHE_BATCH_SIZE );
	$batch_size = max( 1, $batch_size );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		$wpdb->prepare(
			"DELETE FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d",
			RPT_RELATED_CACHE_META_KEY,
			$batch_size
		)
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$remaining = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
			RPT_RELATED_CACHE_META_KEY
		)
	);

	if ( 0 === (int) $remaining ) {
		delete_option( RPT_CACHE_PURGE_PENDING_OPTION );

		return true;
	}

	return false;
}

/**
 * Whitelist sort values used in related post ID queries.
 *
 * @param string $sort_by Raw sort value.
 * @return string Safe ORDER BY clause fragment.
 * @since 5.0.0
 */
function rpt_sanitize_related_sort_by( $sort_by ) {
	$sort_by = trim( (string) $sort_by );

	if ( 'rand()' === $sort_by ) {
		return 'rand()';
	}

	if ( 'post_date' === $sort_by || preg_match( '/^post_date\s+DESC$/i', $sort_by ) ) {
		return 'post_date DESC';
	}

	return 'rand()';
}

/**
 * Clear cached multiple-publishers check.
 *
 * @return void
 * @since 5.0.0
 */
function rpt_flush_multiple_publishers_cache() {
	delete_transient( RPT_MULTIPLE_PUBLISHERS_TRANSIENT );
}
