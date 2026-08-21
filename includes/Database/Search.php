<?php
/**
 * Search class for querying timeline events.
 *
 * @package Vaanilog
 */

namespace Vaanilog\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Search class.
 */
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

		$args['per_page']    = min( 100, max( 1, absint( $args['per_page'] ) ) );
		$args['paged']       = max( 1, absint( $args['paged'] ) );
		$args['type_filter'] = in_array( $args['type_filter'], $allowed_types, true ) ? $args['type_filter'] : '';
		$args['date_filter'] = in_array( $args['date_filter'], $allowed_dates, true ) ? $args['date_filter'] : '';

		$table = Database::table();

		$search      = '';
		$type_filter = '';
		$start_date  = '';
		$end_date    = '';
		$critical    = $args['only_critical'] ? 'critical' : '';

		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
		}

		if ( ! empty( $args['type_filter'] ) ) {
			$type_filter = $args['type_filter'];
		}

		switch ( $args['date_filter'] ) {
			case 'today':
				$today      = current_time( 'Y-m-d' );
				$start_date = $today . ' 00:00:00';
				$end_date   = $today . ' 23:59:59';
				break;

			case 'yesterday':
				$yesterday  = wp_date( 'Y-m-d', strtotime( '-1 day' ), wp_timezone() );
				$start_date = $yesterday . ' 00:00:00';
				$end_date   = $yesterday . ' 23:59:59';
				break;

			case '7days':
				$start_date = wp_date( 'Y-m-d 00:00:00', strtotime( '-7 days' ), wp_timezone() );
				break;

			case '30days':
				$start_date = wp_date( 'Y-m-d 00:00:00', strtotime( '-30 days' ), wp_timezone() );
				break;
		}

		$offset = ( $args['paged'] - 1 ) * $args['per_page'];
		$limit  = $args['per_page'];

		/*
		 * The query structure is fixed - only literal SQL, no interpolated
		 * variables - which is what satisfies WordPress.DB.PreparedSQL.
		 * Each "(%s = %s OR real_condition)" block is an on/off switch:
		 * when the filter value is '', both sides of that first %s = %s
		 * compare equal and the OR short-circuits true, so the predicate
		 * is effectively skipped; when the filter has a value, the first
		 * comparison is false and the real condition after OR is what runs.
		 * %i (added in WP 6.2) safely parameterizes the table name too.
		 */
		$sql = 'SELECT * FROM %i
			WHERE (%s = %s OR object_name LIKE %s OR event_type LIKE %s OR object_type LIKE %s)
			AND (%s = %s OR severity = %s)
			AND (%s = %s OR object_type = %s)
			AND (%s = %s OR created_at >= %s)
			AND (%s = %s OR created_at <= %s)
			ORDER BY created_at DESC, id DESC
			LIMIT %d OFFSET %d';

		$params = array(
			$table,
			$search,
			'',
			$search,
			$search,
			$search,
			$critical,
			'',
			'critical',
			$type_filter,
			'',
			$type_filter,
			$start_date,
			'',
			$start_date,
			$end_date,
			'',
			$end_date,
			$limit,
			$offset,
		);

		$events = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is a fixed literal template above; sniff can't trace the ...$params spread.

		$count_sql = 'SELECT COUNT(*) FROM %i
			WHERE (%s = %s OR object_name LIKE %s OR event_type LIKE %s OR object_type LIKE %s)
			AND (%s = %s OR severity = %s)
			AND (%s = %s OR object_type = %s)
			AND (%s = %s OR created_at >= %s)
			AND (%s = %s OR created_at <= %s)';

		// Same 18 filter params as $params above, minus the trailing LIMIT/OFFSET pair.
		$count_params = array_slice( $params, 0, 18 );

		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$count_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $count_sql is a fixed literal template above; sniff can't trace the ...$params spread.

		$events = Database::decorate_events( $events ? $events : array() );

		return array(
			'events' => $events,
			'total'  => $total,
		);
	}
}
