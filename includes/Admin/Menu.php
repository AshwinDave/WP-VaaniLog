<?php
/**
 * Registers WP VaaniLog admin menus.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Admin;

use WPVaaniLog\Dashboard\Dashboard;
use WPVaaniLog\Timeline\Timeline;
use WPVaaniLog\Settings\Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Registers plugin admin menus.
 */
final class Menu {

	/**
	 * Register menu hooks.
	 *
	 * @return void
	 */
	public function register_menus(): void {
		add_action( 'admin_menu', array( $this, 'register' ) );
	}

	/**
	 * Register plugin admin menus.
	 *
	 * @return void
	 */
	public function register(): void {

		add_menu_page(
			__( 'WP VaaniLog', 'wp-vaanilog' ),
			__( 'Change Monitor', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-dashboard',
			array( new Dashboard(), 'render' ),
			'dashicons-backup',
			58
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'Timeline', 'wp-vaanilog' ),
			__( 'Timeline', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-timeline',
			array( new Timeline(), 'render' )
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'Settings', 'wp-vaanilog' ),
			__( 'Settings', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-settings',
			array( new Settings(), 'render' )
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'About', 'wp-vaanilog' ),
			__( 'About', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-about',
			array( $this, 'about_page' )
		);
	}

	/**
	 * Render the About page.
	 *
	 * @return void
	 */
	public function about_page(): void {
		require VAANILOG_PLUGIN_DIR . 'includes/views/about.php';
	}
}
