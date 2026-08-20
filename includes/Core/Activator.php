<?php
/**
 * Handles plugin activation.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Core;

use WPVaaniLog\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin activation tasks.
 */
final class Activator {

	/**
	 * Activate the plugin.
	 *
	 * @return void
	 */
	public static function activate(): void {
		Database::install();
		update_option( 'vaanilog_db_version', VAANILOG_VERSION );
		Cleanup::schedule();
		flush_rewrite_rules();
	}
}
