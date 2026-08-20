<?php
/**
 * Contract for anything that can persist a change-event row.
 *
 * Logger depends on this interface instead of the concrete Database
 * class, so it never needs to know *how* events are stored (MySQL via
 * $wpdb today, potentially something else tomorrow) and can be unit
 * tested with a fake implementation instead of hitting a real database.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Database;

defined( 'ABSPATH' ) || exit;

interface LoggerRepositoryInterface {

	/**
	 * Persist a single change-event row.
	 *
	 * @param array $data {
	 *     @type string $event_type  Required. e.g. 'post_updated'.
	 *     @type string $object_type Required. e.g. 'post', 'user', 'plugin'.
	 *     @type int    $object_id   Optional. ID of the affected object.
	 *     @type string $object_name Optional. Human-readable name (title, login, etc).
	 *     @type int    $user_id     Optional. Acting user. Defaults to current user.
	 *     @type mixed  $old_value   Optional. Previous value.
	 *     @type mixed  $new_value   Optional. New value.
	 *     @type string $severity    Optional. 'normal' or 'critical'. Default 'normal'.
	 * }
	 * @return int|false Insert ID on success, false on failure.
	 */
	public function insert_event( array $data );
}
