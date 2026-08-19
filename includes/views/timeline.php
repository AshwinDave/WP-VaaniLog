<?php
/**
 * Timeline view.
 *
 * @package WP_Change_Monitor
 * @var array  $events
 * @var int    $total_events
 * @var int    $total_pages
 * @var int    $paged
 * @var string $search
 * @var string $date_filter
 * @var string $type_filter
 * @var bool   $only_critical
 * @var string $page_title
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_page = $only_critical && empty( $_GET['type_filter'] ) ? 'vaanilog-critical' : 'vaanilog-timeline'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
<div class="wrap vaanilog-wrap">
	<h1 class="vaanilog-page-title">
		<span class="dashicons dashicons-backup"></span>
		<?php echo esc_html( $page_title ); ?>
	</h1>

	<form method="get" class="vaanilog-filters vaanilog-filters-sticky">
		<input type="hidden" name="page" value="<?php echo esc_attr( $base_page ); ?>" />

		<input
			type="search"
			name="s"
			class="vaanilog-search-input"
			placeholder="<?php esc_attr_e( 'Search by user, plugin, theme, page, event...', 'wp-vaanilog' ); ?>"
			value="<?php echo esc_attr( $search ); ?>"
		/>

		<select name="date_filter">
			<option value=""><?php esc_html_e( 'All Dates', 'wp-vaanilog' ); ?></option>
			<option value="today" <?php selected( $date_filter, 'today' ); ?>><?php esc_html_e( 'Today', 'wp-vaanilog' ); ?></option>
			<option value="yesterday" <?php selected( $date_filter, 'yesterday' ); ?>><?php esc_html_e( 'Yesterday', 'wp-vaanilog' ); ?></option>
			<option value="7days" <?php selected( $date_filter, '7days' ); ?>><?php esc_html_e( 'Last 7 Days', 'wp-vaanilog' ); ?></option>
			<option value="30days" <?php selected( $date_filter, '30days' ); ?>><?php esc_html_e( 'Last 30 Days', 'wp-vaanilog' ); ?></option>
		</select>

		<?php if ( ! $only_critical ) : ?>
		<select name="type_filter">
			<option value=""><?php esc_html_e( 'All Types', 'wp-vaanilog' ); ?></option>
			<option value="user" <?php selected( $type_filter, 'user' ); ?>><?php esc_html_e( 'Users', 'wp-vaanilog' ); ?></option>
			<option value="plugin" <?php selected( $type_filter, 'plugin' ); ?>><?php esc_html_e( 'Plugins', 'wp-vaanilog' ); ?></option>
			<option value="theme" <?php selected( $type_filter, 'theme' ); ?>><?php esc_html_e( 'Themes', 'wp-vaanilog' ); ?></option>
			<option value="post" <?php selected( $type_filter, 'post' ); ?>><?php esc_html_e( 'Posts', 'wp-vaanilog' ); ?></option>
			<option value="page" <?php selected( $type_filter, 'page' ); ?>><?php esc_html_e( 'Pages', 'wp-vaanilog' ); ?></option>
		</select>
		<?php endif; ?>

		<button type="submit" class="button button-secondary"><?php esc_html_e( 'Filter', 'wp-vaanilog' ); ?></button>
		<?php if ( $search || $date_filter || $type_filter ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $base_page ) ); ?>" class="button-link vaanilog-clear-link"><?php esc_html_e( 'Clear', 'wp-vaanilog' ); ?></a>
		<?php endif; ?>
	</form>

	<p class="vaanilog-result-count">
		<?php
		printf(
			/* translators: %d: number of matching events */
			esc_html( _n( '%d event found', '%d events found', $total_events, 'wp-vaanilog' ) ),
			(int) $total_events
		);
		?>
	</p>

	<div class="vaanilog-timeline vaanilog-timeline-compact">
		<?php if ( empty( $events ) ) : ?>
			<p class="vaanilog-empty"><?php esc_html_e( 'No matching changes found. Try adjusting your search or filters.', 'wp-vaanilog' ); ?></p>
		<?php else : ?>
			<?php
			$last_group = '';
			foreach ( $events as $event ) :
				$dt         = vaanilog_format_datetime( $event->created_at );
				$event_date = gmdate( 'Y-m-d', strtotime( $event->created_at ) );
				$today      = gmdate( 'Y-m-d' );
				$yesterday  = gmdate( 'Y-m-d', strtotime( '-1 day' ) );

				if ( $event_date === $today ) {
					$group_label = __( 'Today', 'wp-vaanilog' );
				} elseif ( $event_date === $yesterday ) {
					$group_label = __( 'Yesterday', 'wp-vaanilog' );
				} else {
					$group_label = $dt['date'];
				}

				if ( $group_label !== $last_group ) :
					$last_group = $group_label;
					?>
					<div class="vaanilog-date-group"><?php echo esc_html( $group_label ); ?></div>
				<?php endif; ?>

				
					<a class="vaanilog-timeline-row <?php echo $event->critical ? 'is-critical' : ''; ?>"
					href="<?php echo esc_url( admin_url( 'admin.php?page=' . $base_page . '&event_id=' . $event->id ) ); ?>"
				>
					<span class="vaanilog-row-icon">
						<span class="dashicons <?php echo esc_attr( vaanilog_event_icon( $event->object_type ) ); ?>"></span>
					</span>

					<span class="vaanilog-row-title">
						<?php echo esc_html( vaanilog_event_label( $event->event_type ) ); ?>
						<?php if ( $event->critical ) : ?>
							<span class="vaanilog-badge-critical"><?php esc_html_e( 'Critical', 'wp-vaanilog' ); ?></span>
						<?php endif; ?>
					</span>

					<span class="vaanilog-row-meta">
						<?php echo esc_html( $event->username ); ?>
						<?php if ( $event->object_name ) : ?>
							&middot; <?php echo esc_html( $event->object_name ); ?>
						<?php endif; ?>
					</span>

					<span class="vaanilog-row-time"><?php echo esc_html( $dt['time'] ); ?></span>

					<span class="vaanilog-row-arrow dashicons dashicons-arrow-right-alt2"></span>
				</a>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="vaanilog-pagination">
			<?php
			$query_args = array(
				'page'        => $base_page,
				's'           => $search,
				'date_filter' => $date_filter,
				'type_filter' => $type_filter,
			);
			for ( $i = 1; $i <= $total_pages; $i++ ) :
				$query_args['paged'] = $i;
				$url                 = add_query_arg( $query_args, admin_url( 'admin.php' ) );
				?>
				<a href="<?php echo esc_url( $url ); ?>" class="vaanilog-page-link <?php echo $i === (int) $paged ? 'is-current' : ''; ?>">
					<?php echo esc_html( $i ); ?>
				</a>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
</div>