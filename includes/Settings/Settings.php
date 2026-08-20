<?php
/**
 * Settings screen.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VAANILOG_Settings
 */
class Settings {

	/**
	 * Render the settings page, handling form submission.
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings = get_option( 'vaanilog_settings', vaanilog_default_settings() );

		$updated = false;

		if (
			isset( $_POST['vaanilog_settings_submit'], $_POST['vaanilog_settings_nonce'] )
		) {
			check_admin_referer( 'vaanilog_save_settings', 'vaanilog_settings_nonce' );

			$allowed_retention = array( 0, 30, 90, 180, 365 );
			$retention_days    = isset( $_POST['log_retention_days'] ) ? absint( $_POST['log_retention_days'] ) : 90;

			$settings = array(
				'track_users'        => isset( $_POST['track_users'] ) ? 1 : 0,
				'track_plugins'      => isset( $_POST['track_plugins'] ) ? 1 : 0,
				'track_themes'       => isset( $_POST['track_themes'] ) ? 1 : 0,
				'track_posts'        => isset( $_POST['track_posts'] ) ? 1 : 0,
				'track_settings'     => isset( $_POST['track_settings'] ) ? 1 : 0,
				'log_retention_days' => in_array( $retention_days, $allowed_retention, true ) ? $retention_days : 90,
			);

			update_option( 'vaanilog_settings', $settings, false );
			$updated = true;
		}

		include VAANILOG_PLUGIN_DIR . 'includes/views/settings.php';
	}
}
