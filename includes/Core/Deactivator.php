<?php
/**
 * Handles plugin deactivation.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Handles plugin deactivation tasks.
 */
final class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * @return void
	 */
	public static function deactivate(): void {
		Cleanup::unschedule();
		flush_rewrite_rules();
	}
}
