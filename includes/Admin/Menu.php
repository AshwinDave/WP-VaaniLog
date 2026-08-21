<?php
/**
 * Registers Vaanilog admin menus.
 *
 * @package Vaanilog
 */

namespace Vaanilog\Admin;

use Vaanilog\Dashboard\Dashboard;
use Vaanilog\Timeline\Timeline;
use Vaanilog\Settings\Settings;

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
			__( 'Vaanilog', 'vaanilog' ),
			__( 'Change Monitor', 'vaanilog' ),
			'manage_options',
			'vaanilog-dashboard',
			array( new Dashboard(), 'render' ),
			'dashicons-backup',
			58
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'Timeline', 'vaanilog' ),
			__( 'Timeline', 'vaanilog' ),
			'manage_options',
			'vaanilog-timeline',
			array( new Timeline(), 'render' )
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'Settings', 'vaanilog' ),
			__( 'Settings', 'vaanilog' ),
			'manage_options',
			'vaanilog-settings',
			array( new Settings(), 'render' )
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'About', 'vaanilog' ),
			__( 'About', 'vaanilog' ),
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
		require dirname( __DIR__ ) . '/views/about.php';
	}
}
