<?php
/**
 * Admin Assets
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Admin;

defined( 'ABSPATH' ) || exit;

class Assets {

	/**
	 * Register hooks.
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current page hook.
	 */
	public function enqueue( string $hook ): void {
		$screen = get_current_screen();

		if ( ! $screen || strpos( $screen->id, 'vaanilog' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'vaanilog-admin',
			VAANILOG_PLUGIN_URL . 'assets/css/admin.css',
			[],
			VAANILOG_VERSION
		);
	}
}
