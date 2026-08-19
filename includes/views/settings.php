<?php
/**
 * Settings view.
 *
 * @package WP_Change_Monitor
 * @var array $settings
 * @var bool  $updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tracking_options = array(
	'track_users'    => array(
		'label' => __( 'User Tracking', 'wp-vaanilog' ),
		'desc'  => __( 'Logins, logouts, role and profile changes.', 'wp-vaanilog' ),
		'icon'  => 'dashicons-admin-users',
	),
	'track_plugins'  => array(
		'label' => __( 'Plugin Tracking', 'wp-vaanilog' ),
		'desc'  => __( 'Plugin activation, deactivation, and updates.', 'wp-vaanilog' ),
		'icon'  => 'dashicons-admin-plugins',
	),
	'track_themes'   => array(
		'label' => __( 'Theme Tracking', 'wp-vaanilog' ),
		'desc'  => __( 'Theme switches and updates.', 'wp-vaanilog' ),
		'icon'  => 'dashicons-admin-appearance',
	),
	'track_posts'    => array(
		'label' => __( 'Post Tracking', 'wp-vaanilog' ),
		'desc'  => __( 'Post and page creation, edits, and deletions.', 'wp-vaanilog' ),
		'icon'  => 'dashicons-admin-post',
	),
	'track_settings' => array(
		'label' => __( 'Settings Tracking', 'wp-vaanilog' ),
		'desc'  => __( 'Changes to WordPress core settings.', 'wp-vaanilog' ),
		'icon'  => 'dashicons-admin-generic',
	),
);
?>
<div class="wrap vaanilog-wrap">
	<h1 class="vaanilog-page-title">
		<span class="dashicons dashicons-admin-settings"></span>
		<?php esc_html_e( 'Settings', 'wp-vaanilog' ); ?>
	</h1>

	<?php if ( $updated ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'wp-vaanilog' ); ?></p>
		</div>
	<?php endif; ?>

	<form method="post" class="vaanilog-settings-form vaanilog-settings-form-cards">
		<?php wp_nonce_field( 'vaanilog_save_settings', 'vaanilog_settings_nonce' ); ?>

		<h2 class="vaanilog-section-title"><?php esc_html_e( 'Tracking', 'wp-vaanilog' ); ?></h2>

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

		<div class="vaanilog-save-bar">
			<?php submit_button( __( 'Save Settings', 'wp-vaanilog' ), 'primary', 'vaanilog_settings_submit', false ); ?>
		</div>
	</form>
</div>