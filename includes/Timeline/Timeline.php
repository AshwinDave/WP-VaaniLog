<?php
/**
 * Timeline screen with search and filters.
 *
 * @package Vaanilog
 */

namespace Vaanilog\Timeline;

use Vaanilog\Database\Database;
use Vaanilog\Database\Search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timeline screen handler.
 */
class Timeline {

	/**
	 * Number of events shown per page.
	 *
	 * @var int
	 */
	private int $per_page = 20;

	/**
	 * Render the timeline page.
	 *
	 * @param bool $critical_only Whether to force the "Critical Only" filter.
	 * @return void
	 */
	public function render( bool $critical_only = false ): void {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// If a single event ID is requested, show details screen instead.
		if ( ! empty( $_GET['event_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->render_details( absint( $_GET['event_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$search        = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_filter   = isset( $_GET['date_filter'] ) ? sanitize_key( $_GET['date_filter'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type_filter   = isset( $_GET['type_filter'] ) ? sanitize_key( $_GET['type_filter'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$only_critical = $critical_only || ( isset( $_GET['type_filter'] ) && 'critical' === $_GET['type_filter'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged         = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$results = Search::query(
			array(
				'search'        => $search,
				'date_filter'   => $date_filter,
				'type_filter'   => $only_critical ? '' : $type_filter,
				'only_critical' => $only_critical,
				'per_page'      => $this->per_page,
				'paged'         => $paged,
			)
		);

		$events       = $results['events'];
		$total_events = $results['total'];
		$total_pages  = (int) ceil( $total_events / $this->per_page );
		$page_title   = $critical_only ? __( 'Critical Changes', 'vaanilog' ) : __( 'Timeline', 'vaanilog' );

		include __DIR__ . '/../views/timeline.php';
	}

	/**
	 * Render the single event details screen.
	 *
	 * @param int $event_id Event row ID.
	 * @return void
	 */
	private function render_details( int $event_id ): void {

		global $wpdb;

		$table = Database::table();

		$event = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %i WHERE id = %d',
				$table,
				$event_id
			)
		);

		if ( $event ) {
			$event = Database::decorate_event( $event );
		}

		include __DIR__ . '/../views/event-details.php';
	}
}
