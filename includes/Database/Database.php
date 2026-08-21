<?php
/**
 * Provides database persistence and query helpers for VaaniLog.
 *
 * @package Vaanilog
 */

namespace Vaanilog\Database;

defined( 'ABSPATH' ) || exit;

/**
 * $wpdb-backed implementation of LoggerRepositoryInterface.
 *
 * There the insert_event() is an instance method (not static) specifically so
 * Logger can depend on the LoggerRepositoryInterface contract instead
 * of this concrete class - see Logger::__construct(). Every other
 * method here is a read/utility helper called from several unrelated
 * classes (Dashboard, Timeline, Search, Activator, Uninstaller) and is
 * kept static since those call sites have no need for a Database
 * instance and static keeps them simple.
 */
final class Database implements LoggerRepositoryInterface {

	/**
	 * Insert a change-event row into the log table.
	 *
	 * This is the single write-path used by the Logger. Any hook that wants
	 * to record a change must go through this method.
	 *
	 * @param array $data Event data to persist.

	 * @return int|false Insert ID on success, false on failure.
	 */
	public function insert_event( array $data ) {

		global $wpdb;

		if ( empty( $data['event_type'] ) || empty( $data['object_type'] ) ) {
			return false;
		}

		$defaults = array(
			'object_id'   => null,
			'object_name' => '',
			'user_id'     => get_current_user_id(),
			'old_value'   => '',
			'new_value'   => '',
			'severity'    => 'normal',
		);

		$data = wp_parse_args( $data, $defaults );

		// Values can be arrays/objects (e.g. option values) - store as scalar text.
		// Normalize structured values without allowing arbitrary objects to be
		// serialized. Sensitive data should already be redacted by the Logger;
		// this final boundary also prevents accidental object serialization.
		$old_value = is_scalar( $data['old_value'] ) || null === $data['old_value']
			? (string) $data['old_value']
			: wp_json_encode( vaanilog_redact_sensitive_value( $data['old_value'] ) );
		$new_value = is_scalar( $data['new_value'] ) || null === $data['new_value']
			? (string) $data['new_value']
			: wp_json_encode( vaanilog_redact_sensitive_value( $data['new_value'] ) );

		// Guard against runaway row sizes from large values.
		$max_len   = 4000;
		$old_value = function_exists( 'mb_substr' ) ? mb_substr( (string) $old_value, 0, $max_len ) : substr( (string) $old_value, 0, $max_len );
		$new_value = function_exists( 'mb_substr' ) ? mb_substr( (string) $new_value, 0, $max_len ) : substr( (string) $new_value, 0, $max_len );

		$inserted = $wpdb->insert(
			self::table(),
			array(
				'event_type'  => sanitize_key( $data['event_type'] ),
				'object_type' => sanitize_key( $data['object_type'] ),
				'object_id'   => $data['object_id'] ? absint( $data['object_id'] ) : null,
				'object_name' => sanitize_text_field( (string) $data['object_name'] ),
				'user_id'     => absint( $data['user_id'] ),
				'old_value'   => $old_value,
				'new_value'   => $new_value,
				'severity'    => in_array( $data['severity'], array( 'normal', 'critical' ), true ) ? $data['severity'] : 'normal',
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%d', '%s', '%d', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	/**
	 * Attach computed ->username and ->critical properties to a raw log
	 * Row, since the table itself only stores user_id and severity.
	 * The views (dashboard.php, timeline.php, event-details.php) read
	 * ->username / ->critical, so every read-path must call this.
	 *
	 * @param object $event Raw row from $wpdb.
	 * @return object
	 */
	public static function decorate_event( object $event ): object {

		$event->username = vaanilog_get_username( $event->user_id ?? 0 );
		$event->critical = isset( $event->severity ) && 'critical' === $event->severity;

		return $event;
	}

	/**
	 * Decorate an array of rows. @see self::decorate_event().
	 *
	 * @param object[] $events Raw rows from $wpdb.
	 * @return object[]
	 */
	public static function decorate_events( array $events ): array {

		foreach ( $events as $event ) {
			self::decorate_event( $event );
		}

		return $events;
	}

	/**
	 * Delete log rows older than the given number of days. Used by the
	 * daily retention cron (see Core\Cleanup) so the log table doesn't
	 * grow without bound - both a storage concern and a data-minimization
	 * one, since old rows may still reference removed users/content.
	 *
	 * @param int $days Rows older than this many days are deleted.
	 * @return int Number of rows deleted (0 on failure or if $days <= 0).
	 */
	public static function prune_older_than( int $days ): int {

		if ( $days <= 0 ) {
			return 0;
		}

		global $wpdb;

		$table  = self::table();
		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		$deleted = $wpdb->query(
			$wpdb->prepare(
				'DELETE FROM %i WHERE created_at < %s',
				$table,
				$cutoff
			)
		);

		return false === $deleted ? 0 : (int) $deleted;
	}

	/**
	 * Install the database schema.
	 */
	public static function install(): void {

		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = self::table();

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			event_type VARCHAR(100) NOT NULL,
			object_type VARCHAR(100) NOT NULL,
			object_id BIGINT UNSIGNED NULL,
			object_name VARCHAR(255) NULL,
			user_id BIGINT UNSIGNED NULL,
			old_value LONGTEXT NULL,
			new_value LONGTEXT NULL,
			severity VARCHAR(20) DEFAULT 'normal',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY event_type (event_type),
			KEY object_type (object_type),
			KEY object_id (object_id),
			KEY user_id (user_id),
			KEY created_at (created_at),
			KEY severity (severity)
		) {$charset_collate};";

		dbDelta( $sql );
	}

	/**
	 * Run the installer if the stored schema version doesn't match the
	 * running plugin version. dbDelta() is safe to re-run (it diffs the
	 * existing table against the target schema), so this keeps sites
	 * that never deactivate/reactivate the plugin in sync with schema
	 * changes shipped in updates, instead of only ever installing once.
	 *
	 * Cheap to call on every request: a single autoloaded option read
	 * when already up to date.
	 */
	public static function maybe_upgrade(): void {

		if ( get_option( 'vaanilog_db_version' ) === VAANILOG_VERSION ) {
			return;
		}

		self::install();

		update_option( 'vaanilog_db_version', VAANILOG_VERSION );
	}

	/**
	 * Get log table name.
	 *
	 * @return string
	 */
	public static function table(): string {

		global $wpdb;

		return $wpdb->prefix . 'vaanilog_logs';
	}
}
