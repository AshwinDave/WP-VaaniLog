<?php
/**
 * Event details view.
 *
 * @package Vaanilog
 * @var object|null $event
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap vaanilog-wrap">
	<h1 class="vaanilog-page-title">
		<span class="dashicons dashicons-info"></span>
		<?php esc_html_e( 'Event Details', 'vaanilog' ); ?>
	</h1>

	<?php if ( ! $event ) : ?>
		<p class="vaanilog-empty"><?php esc_html_e( 'Event not found.', 'vaanilog' ); ?></p>
	<?php else : ?>
		<?php $dt = vaanilog_format_datetime( $event->created_at ); ?>

		<div class="vaanilog-details-card <?php echo esc_attr( $event->critical ? 'is-critical' : '' ); ?>">
			<table class="widefat vaanilog-details-table">
				<tbody>
					<tr>
						<th><?php esc_html_e( 'Event Type', 'vaanilog' ); ?></th>
						<td>
							<?php echo esc_html( vaanilog_event_label( $event->event_type ) ); ?>
							<?php if ( $event->critical ) : ?>
								<span class="vaanilog-badge-critical"><?php esc_html_e( 'Critical', 'vaanilog' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'User', 'vaanilog' ); ?></th>
						<td><?php echo esc_html( $event->username ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Date', 'vaanilog' ); ?></th>
						<td><?php echo esc_html( $dt['date'] ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Time', 'vaanilog' ); ?></th>
						<td><?php echo esc_html( $dt['time'] ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Object Name', 'vaanilog' ); ?></th>
						<td><?php echo esc_html( $event->object_name ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Critical Level', 'vaanilog' ); ?></th>
						<td><?php echo $event->critical ? esc_html__( 'Critical', 'vaanilog' ) : esc_html__( 'Normal', 'vaanilog' ); ?></td>
					</tr>
				</tbody>
			</table>

			<?php if ( '' !== (string) $event->old_value || '' !== (string) $event->new_value ) : ?>
				<h2 class="vaanilog-section-title"><?php esc_html_e( 'Before vs After', 'vaanilog' ); ?></h2>
				<div class="vaanilog-diff">
					<div class="vaanilog-diff-old">
						<span class="vaanilog-diff-label"><?php esc_html_e( 'Old Value', 'vaanilog' ); ?></span>
						<div class="vaanilog-diff-value"><?php echo esc_html( $event->old_value ); ?></div>
					</div>
					<div class="vaanilog-diff-arrow">&rarr;</div>
					<div class="vaanilog-diff-new">
						<span class="vaanilog-diff-label"><?php esc_html_e( 'New Value', 'vaanilog' ); ?></span>
						<div class="vaanilog-diff-value"><?php echo esc_html( $event->new_value ); ?></div>
					</div>
				</div>
			<?php endif; ?>
		</div>

		<p>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=vaanilog-timeline' ) ); ?>" class="button">
				&larr; <?php esc_html_e( 'Back to Timeline', 'vaanilog' ); ?>
			</a>
		</p>
	<?php endif; ?>
</div>
