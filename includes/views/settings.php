<?php
/**
 * Settings view.
 *
 * @package Vaanilog
 * @var array $settings
 * @var bool  $updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tracking_options = array(
	'track_users'    => array(
		'label' => __( 'User Tracking', 'vaanilog' ),
		'desc'  => __( 'Logins, logouts, role and profile changes.', 'vaanilog' ),
		'icon'  => 'dashicons-admin-users',
	),
	'track_plugins'  => array(
		'label' => __( 'Plugin Tracking', 'vaanilog' ),
		'desc'  => __( 'Plugin activation, deactivation, and updates.', 'vaanilog' ),
		'icon'  => 'dashicons-admin-plugins',
	),
	'track_themes'   => array(
		'label' => __( 'Theme Tracking', 'vaanilog' ),
		'desc'  => __( 'Theme switches and updates.', 'vaanilog' ),
		'icon'  => 'dashicons-admin-appearance',
	),
	'track_posts'    => array(
		'label' => __( 'Post Tracking', 'vaanilog' ),
		'desc'  => __( 'Post and page creation, edits, and deletions.', 'vaanilog' ),
		'icon'  => 'dashicons-admin-post',
	),
	'track_settings' => array(
		'label' => __( 'Settings Tracking', 'vaanilog' ),
		'desc'  => __( 'Changes to WordPress core settings.', 'vaanilog' ),
		'icon'  => 'dashicons-admin-generic',
	),
);
?>
<div class="wrap vaanilog-wrap">
	<h1 class="vaanilog-page-title">
		<span class="dashicons dashicons-admin-settings"></span>
		<?php esc_html_e( 'Settings', 'vaanilog' ); ?>
	</h1>

	<?php if ( $updated ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'vaanilog' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" class="vaanilog-settings-form vaanilog-settings-form-cards">
		<?php wp_nonce_field( 'vaanilog_save_settings', 'vaanilog_settings_nonce' ); ?>

		<h2 class="vaanilog-section-title"><?php esc_html_e( 'Tracking', 'vaanilog' ); ?></h2>

		<div class="vaanilog-toggle-list">
			<?php foreach ( $tracking_options as $key => $opt ) : ?>
				<label class="vaanilog-toggle-row">
					<span class="vaanilog-toggle-icon">
						<span class="dashicons <?php echo esc_attr( $opt['icon'] ); ?>"></span>
					</span>

					<span class="vaanilog-toggle-text">
						<span class="vaanilog-toggle-label"><?php echo esc_html( $opt['label'] ); ?></span>
						<span class="vaanilog-toggle-desc"><?php echo esc_html( $opt['desc'] ); ?></span>
					</span>

					<span class="vaanilog-switch">
						<input type="checkbox" name="<?php echo esc_attr( $key ); ?>" value="1" <?php checked( ! empty( $settings[ $key ] ) ); ?> />
						<span class="vaanilog-switch-track"></span>
					</span>
				</label>
			<?php endforeach; ?>
		</div>

		<h2 class="vaanilog-section-title"><?php esc_html_e( 'Data Retention', 'vaanilog' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Older log entries are removed automatically once a day so the log table does not grow forever.', 'vaanilog' ); ?>
		</p>
		<p>
			<label for="vaanilog-log-retention-days">
				<?php esc_html_e( 'Keep activity logs for', 'vaanilog' ); ?>
			</label><br />
			<?php $retention = isset( $settings['log_retention_days'] ) ? (int) $settings['log_retention_days'] : 90; ?>
			<select name="log_retention_days" id="vaanilog-log-retention-days">
				<option value="30" <?php selected( $retention, 30 ); ?>><?php esc_html_e( '30 days', 'vaanilog' ); ?></option>
				<option value="90" <?php selected( $retention, 90 ); ?>><?php esc_html_e( '90 days', 'vaanilog' ); ?></option>
				<option value="180" <?php selected( $retention, 180 ); ?>><?php esc_html_e( '180 days', 'vaanilog' ); ?></option>
				<option value="365" <?php selected( $retention, 365 ); ?>><?php esc_html_e( '1 year', 'vaanilog' ); ?></option>
				<option value="0" <?php selected( $retention, 0 ); ?>><?php esc_html_e( 'Forever (not recommended)', 'vaanilog' ); ?></option>
			</select>
		</p>

		<div class="vaanilog-save-bar">
			<?php submit_button( __( 'Save Settings', 'vaanilog' ), 'primary', 'vaanilog_settings_submit', false ); ?>
		</div>
	</form>
</div>