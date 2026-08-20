<?php
/**
 * Schedules and runs the daily log-retention cron job.
 *
 * Without this, the log table grows forever - a storage problem, and
 * a privacy/data-minimization one too, since rows reference users,
 * posts, and option values that may since have been removed for
 * other reasons. Retention period is user-configurable in Settings
 * (default 90 days, 0 = keep forever).
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Core;

use WPVaaniLog\Database\Database;

defined( 'ABSPATH' ) || exit;

final class Cleanup {

	/**
	 * Cron hook name.
	 */
	const CRON_HOOK = 'vaanilog_prune_old_events';

	/**
	 * Register the cron callback. Safe to call on every request -
	 * add_action() itself is cheap; scheduling happens separately in
	 * self::schedule(), called only on activation.
	 */
	public function register(): void {
		add_action( self::CRON_HOOK, array( $this, 'run' ) );
	}

	/**
	 * Schedule the daily event, if not already scheduled. Called from
	 * Activator so re-activating never creates duplicate schedules.
	 */
	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( time(), 'daily', self::CRON_HOOK );
		}
	}

	/**
	 * Remove the scheduled event. Called from Deactivator (and
	 * defensively from Uninstaller) so no orphaned cron event is left
	 * behind trying to fire a callback that may no longer exist.
	 */
	public static function unschedule(): void {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}

		wp_clear_scheduled_hook( self::CRON_HOOK );
	}

	/**
	 * Cron callback: delete rows older than the configured retention
	 * period. A retention of 0 means "keep forever" - skip pruning.
	 */
	public function run(): void {

		$settings = get_option( 'vaanilog_settings', vaanilog_default_settings() );
		$days     = isset( $settings['log_retention_days'] ) ? (int) $settings['log_retention_days'] : 90;

		if ( $days <= 0 ) {
			return;
		}

		Database::prune_older_than( $days );
	}
}
