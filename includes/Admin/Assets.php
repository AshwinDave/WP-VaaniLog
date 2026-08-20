<?php
/**
 * Admin asset registration.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Registers plugin admin assets.
 */
class Assets {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current page hook.
	 * @return void
	 */
	public function enqueue( string $hook ): void {
		$screen = get_current_screen();

		if ( ! $screen || false === strpos( $screen->id, 'vaanilog' ) ) {
			return;
		}

		wp_enqueue_style(
			'vaanilog-admin',
			VAANILOG_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			VAANILOG_VERSION
		);
	}
}
