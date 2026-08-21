<?php
/**
 * Handles full cleanup when the plugin is deleted from wp-admin.
 *
 * This class was referenced by uninstall.php but never existed, which
 * meant deleting the plugin threw a fatal "Class not found" error.
 *
 * @package Vaanilog
 */

namespace Vaanilog\Core;

use Vaanilog\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin cleanup during uninstall.
 */
final class Uninstaller {

	/**
	 * Remove everything the plugin created: the log table and its
	 * settings option. Runs only when the user explicitly deletes the
	 * plugin (WP_UNINSTALL_PLUGIN check happens in uninstall.php).
	 */
	public static function uninstall(): void {

		global $wpdb;

		$table = Database::table();

		// Defensive: normally Deactivator already clears this on
		// deactivation, but plugins can be force-deleted (e.g. via
		// filesystem/WP-CLI) without WordPress ever firing the
		// deactivation hook, which would otherwise leave an orphaned
		// cron event trying to call code that no longer exists.
		Cleanup::unschedule();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query(
			$wpdb->prepare(
				'DROP TABLE IF EXISTS %i',
				$table
			)
		);

		delete_option( 'vaanilog_settings' );
		delete_option( 'vaanilog_db_version' );

		// In case a multisite network activation ever stores these too.
		delete_site_option( 'vaanilog_settings' );
		delete_site_option( 'vaanilog_db_version' );
	}
}
