<?php
/**
 * Handles full cleanup when the plugin is deleted from wp-admin.
 *
 * This class was referenced by uninstall.php but never existed, which
 * meant deleting the plugin threw a fatal "Class not found" error.
 *
 * @package WP_Change_Monitor
 */

namespace WPVaaniLog\Core;

use WPVaaniLog\Database\Database;

defined( 'ABSPATH' ) || exit;

final class Uninstaller {

	/**
	 * Remove everything the plugin created: the log table and its
	 * settings option. Runs only when the user explicitly deletes the
	 * plugin (WP_UNINSTALL_PLUGIN check happens in uninstall.php).
	 */
	public static function uninstall(): void {

		global $wpdb;

		$table = Database::table();

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );

		delete_option( 'vaanilog_settings' );

		// In case a multisite network activation ever stores this too.
		delete_site_option( 'vaanilog_settings' );
	}
}
