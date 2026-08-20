<?php
/**
 * Dashboard screen with summary cards.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Dashboard;

defined( 'ABSPATH' ) || exit;

use WPVaaniLog\Database\Database;

/**
 * Dashboard screen handler.
 */
class Dashboard {

	/**
	 * Render the dashboard page.
	 *
	 * @return void
	 */
	public function render(): void {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$stats  = $this->get_stats();
		$recent = $this->get_recent_events( 8 );

		$system = array(
			'wp_version'   => get_bloginfo( 'version' ),
			'php_version'  => PHP_VERSION,
			'memory_limit' => ini_get( 'memory_limit' ),
			'wp_debug'     => defined( 'WP_DEBUG' ) && WP_DEBUG,
			'cron'         => defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 'Disabled' : 'Enabled',
			'theme'        => wp_get_theme()->get( 'Name' ),
			'plugins'      => count( get_option( 'active_plugins', array() ) ),
		);

		$chart = $this->get_last_seven_days();

		require VAANILOG_PLUGIN_DIR . 'includes/views/dashboard.php';
	}

	/**
	 * Calculate summary card numbers.
	 *
	 * @return array
	 */
	private function get_stats(): array {

		global $wpdb;

		$table       = Database::table();
		$today_start = current_time( 'Y-m-d' ) . ' 00:00:00';

		$total_today = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %i WHERE created_at >= %s',
				$table,
				$today_start
			)
		);

		$critical_today = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE created_at >= %s AND severity = 'critical'",
				$table,
				$today_start
			)
		);

		$plugin_updates_today = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE created_at >= %s AND object_type = 'plugin' AND event_type = 'plugin_updated'",
				$table,
				$today_start
			)
		);

		$theme_updates_today = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE created_at >= %s AND object_type = 'theme' AND event_type = 'theme_updated'",
				$table,
				$today_start
			)
		);

		$content_changes_today = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM %i WHERE created_at >= %s AND object_type IN ('post', 'page')",
				$table,
				$today_start
			)
		);

		return array(
			'total_today'           => $total_today,
			'critical_today'        => $critical_today,
			'plugin_updates_today'  => $plugin_updates_today,
			'theme_updates_today'   => $theme_updates_today,
			'content_changes_today' => $content_changes_today,
		);
	}

	/**
	 * Get the most recent events for the dashboard preview.
	 *
	 * @param int $limit Number of events to fetch.
	 * @return array
	 */
	private function get_recent_events( int $limit = 8 ): array {

		global $wpdb;

		$table = Database::table();
		$limit = max( 1, min( 100, absint( $limit ) ) );

		$events = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id, event_type, object_type, object_name, user_id, severity, created_at
				FROM %i
				ORDER BY created_at DESC
				LIMIT %d',
				$table,
				$limit
			)
		);

		return Database::decorate_events( $events ? $events : array() );
	}

	/**
	 * Get last 7 days activity.
	 *
	 * @return array
	 */
	private function get_last_seven_days(): array {

		global $wpdb;

		$table = Database::table();

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT
					DATE(created_at) AS event_date,
					COUNT(*) AS total
				FROM %i
				WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 DAY)
				GROUP BY DATE(created_at)
				ORDER BY event_date ASC',
				$table
			)
		);

		$data = array();

		for ( $i = 6; $i >= 0; $i-- ) {
			$date         = gmdate( 'Y-m-d', strtotime( "-{$i} days" ) );
			$data[ $date ] = 0;
		}

		foreach ( $rows as $row ) {
			$data[ $row->event_date ] = (int) $row->total;
		}

		return $data;
	}
}
