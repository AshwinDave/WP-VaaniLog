<?php
/**
 * Search class for querying timeline events.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Database;

defined( 'ABSPATH' ) || exit;

class Search {

	/**
	 * Query events with search, filters, and pagination.
	 *
	 * @param array $args Query arguments.
	 * @return array Array with 'events' and 'total' keys.
	 */
	public static function query( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'search'        => '',
			'date_filter'   => '',
			'type_filter'   => '',
			'only_critical' => false,
			'per_page'      => 20,
			'paged'         => 1,
		);

		$args = wp_parse_args( $args, $defaults );

		$allowed_types = array( 'user', 'plugin', 'theme', 'post', 'page', 'core', 'setting' );
		$allowed_dates = array( 'today', 'yesterday', '7days', '30days' );

		$args['per_page'] = min( 100, max( 1, absint( $args['per_page'] ) ) );
		$args['paged']    = max( 1, absint( $args['paged'] ) );
		$args['type_filter'] = in_array( $args['type_filter'], $allowed_types, true ) ? $args['type_filter'] : '';
		$args['date_filter'] = in_array( $args['date_filter'], $allowed_dates, true ) ? $args['date_filter'] : '';

		$table = Database::table();
		$where = 'WHERE 1=1';

		// Search query
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where .= $wpdb->prepare(
				' AND (object_name LIKE %s OR event_type LIKE %s OR object_type LIKE %s)',
				$search,
				$search,
				$search
			);
		}

		// Critical filter
		if ( $args['only_critical'] ) {
			$where .= ' AND severity = "critical"';
		}

		// Type filter
		if ( ! empty( $args['type_filter'] ) ) {
			$where .= $wpdb->prepare( ' AND object_type = %s', $args['type_filter'] );
		}

		// Date filter.
		if ( ! empty( $args['date_filter'] ) ) {
			$start_date = null;
			$end_date   = null;

			switch ( $args['date_filter'] ) {
				case 'today':
					$start_date = current_time( 'Y-m-d' ) . ' 00:00:00';
					$end_date   = current_time( 'Y-m-d' ) . ' 23:59:59';
					break;
				case 'yesterday':
					$start_date = wp_date( 'Y-m-d 00:00:00', strtotime( '-1 day' ), wp_timezone() );
					$end_date   = wp_date( 'Y-m-d 23:59:59', strtotime( '-1 day' ), wp_timezone() );
					break;
				case '7days':
					$start_date = wp_date( 'Y-m-d 00:00:00', strtotime( '-7 days' ), wp_timezone() );
					break;
				case '30days':
					$start_date = wp_date( 'Y-m-d 00:00:00', strtotime( '-30 days' ), wp_timezone() );
					break;
			}

			if ( $start_date ) {
				$where .= $wpdb->prepare( ' AND created_at >= %s', $start_date );
			}
			if ( $end_date ) {
				$where .= $wpdb->prepare( ' AND created_at <= %s', $end_date );
			}
		}

		// Get total count
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );

		// Pagination
		$offset = ( $args['paged'] - 1 ) * $args['per_page'];
		$limit  = $args['per_page'];

		// Both pagination values are bounded integers and are still passed
		// through prepare() to keep this query safe if the implementation changes.
		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} {$where} ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d",
			$limit,
			$offset
		);

		$events = $wpdb->get_results( $sql );

		$events = Database::decorate_events( $events ?: array() );

		return array(
			'events' => $events,
			'total'  => $total,
		);
	}
}
