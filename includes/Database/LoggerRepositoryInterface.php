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
	 * @param array $data Event data to persist.

	 * @return int|false Insert ID on success, false on failure.
	 */
	public function insert_event( array $data );
}
