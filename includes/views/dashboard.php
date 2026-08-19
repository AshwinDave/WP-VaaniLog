<?php
/**
 * Dashboard view.
 *
 * @package WP_Change_Monitor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap vaanilog-dashboard">

	<div class="vaanilog-hero">

		<div class="vaanilog-hero-left">

			<h1>WP VaaniLog</h1>

			<p>
				Monitor every important change happening on your WordPress website.
			</p>

			<div class="vaanilog-live-status">
				<span class="vaanilog-live-dot"></span>
				Live Monitoring
			</div>

		</div>

		<div class="vaanilog-hero-right">

			<a href="<?php echo esc_url( admin_url( 'admin.php?page=vaanilog-settings' ) ); ?>" class="button button-primary">
				Settings
			</a>

		</div>

	</div>


	<div class="vaanilog-stats-grid">

		<div class="vaanilog-stat-card">
			<span class="label">Today's Changes</span>
			<h2><?php echo esc_html( $stats['total_today'] ); ?></h2>
		</div>

		<div class="vaanilog-stat-card critical">
			<span class="label">Critical</span>
			<h2><?php echo esc_html( $stats['critical_today'] ); ?></h2>
		</div>

		<div class="vaanilog-stat-card">
			<span class="label">Plugin Updates</span>
			<h2><?php echo esc_html( $stats['plugin_updates_today'] ); ?></h2>
		</div>

		<div class="vaanilog-stat-card">
			<span class="label">Theme Updates</span>
			<h2><?php echo esc_html( $stats['theme_updates_today'] ); ?></h2>
		</div>

		<div class="vaanilog-stat-card">
			<span class="label">Content Changes</span>
			<h2><?php echo esc_html( $stats['content_changes_today'] ); ?></h2>
		</div>

	</div>


	<div class="vaanilog-dashboard-grid">

		<div class="vaanilog-panel">

			<h2>Activity Overview</h2>

			<?php
			$chart_max = max( 1, max( array_values( $chart ) ) );
			?>

			<div class="vaanilog-activity-chart" aria-label="<?php esc_attr_e( 'Changes over the last seven days', 'wp-vaanilog' ); ?>">
				<?php foreach ( $chart as $date => $total ) : ?>
					<div class="vaanilog-activity-day">
						<div class="vaanilog-activity-bar-wrap">
							<div class="vaanilog-activity-bar" style="height: <?php echo esc_attr( (string) max( 4, round( ( $total / $chart_max ) * 100 ) ) ); ?>%;"></div>
						</div>
						<span><?php echo esc_html( wp_date( 'M j', strtotime( $date ) ) ); ?></span>
						<strong><?php echo esc_html( $total ); ?></strong>
					</div>
				<?php endforeach; ?>
			</div>

		</div>

		<div class="vaanilog-panel">

			<h2>System Health</h2>

			<div class="vaanilog-health-grid">

				<div class="vaanilog-health-item">
					<span class="vaanilog-health-label">WordPress</span>
					<span class="vaanilog-health-value"><?php echo esc_html( $system['wp_version'] ); ?></span>
				</div>

				<div class="vaanilog-health-item">
					<span class="vaanilog-health-label">PHP</span>
					<span class="vaanilog-health-value"><?php echo esc_html( $system['php_version'] ); ?></span>
				</div>

				<div class="vaanilog-health-item">
					<span class="vaanilog-health-label">Memory Limit</span>
					<span class="vaanilog-health-value"><?php echo esc_html( $system['memory_limit'] ); ?></span>
				</div>

				<div class="vaanilog-health-item">
					<span class="vaanilog-health-label">Debug</span>
					<span class="vaanilog-health-value <?php echo $system['wp_debug'] ? 'is-warning' : ''; ?>">
						<?php echo $system['wp_debug'] ? 'Enabled' : 'Disabled'; ?>
					</span>
				</div>

				<div class="vaanilog-health-item">
					<span class="vaanilog-health-label">WP Cron</span>
					<span class="vaanilog-health-value"><?php echo esc_html( $system['cron'] ); ?></span>
				</div>

				<div class="vaanilog-health-item">
					<span class="vaanilog-health-label">Theme</span>
					<span class="vaanilog-health-value"><?php echo esc_html( $system['theme'] ); ?></span>
				</div>

				<div class="vaanilog-health-item vaanilog-health-item-full">
					<span class="vaanilog-health-label">Active Plugins</span>
					<span class="vaanilog-health-value"><?php echo esc_html( $system['plugins'] ); ?></span>
				</div>

			</div>

		</div>

	</div>


	<div class="vaanilog-panel vaanilog-timeline-panel">

		<h2>Recent Activity</h2>

		<?php if ( empty( $recent ) ) : ?>

			<p>No activity found.</p>

		<?php else : ?>

			<div class="vaanilog-timeline vaanilog-timeline-scroll">

				<?php foreach ( array_slice( $recent, 0, 6 ) as $event ) : ?>

					<?php $dt = vaanilog_format_datetime( $event->created_at ); ?>

					<div class="vaanilog-timeline-item <?php echo $event->critical ? 'is-critical' : ''; ?>">

						<div class="vaanilog-timeline-icon">

							<span class="dashicons <?php echo esc_attr( vaanilog_event_icon( $event->object_type ) ); ?>"></span>

						</div>

						<div class="vaanilog-timeline-content">

							<h4>

								<?php echo esc_html( vaanilog_event_label( $event->event_type ) ); ?>

								<?php if ( $event->critical ) : ?>

									<span class="vaanilog-critical-badge">

										Critical

									</span>

								<?php endif; ?>

							</h4>

							<p>

								<strong><?php echo esc_html( $event->username ); ?></strong>

								<?php if ( ! empty( $event->object_name ) ) : ?>

									•

									<?php echo esc_html( $event->object_name ); ?>

								<?php endif; ?>

							</p>

							<small>

								<?php echo esc_html( $dt['date'] . ' ' . $dt['time'] ); ?>

							</small>

						</div>

					</div>

				<?php endforeach; ?>

			</div>

		<?php endif; ?>

		<p style="margin-top:20px;">

			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=vaanilog-timeline' ) ); ?>">

				View Full Timeline

			</a>

		</p>

	</div>

</div>

