<?php

namespace WPVaaniLog\Admin;

use WPVaaniLog\Dashboard\Dashboard;
use WPVaaniLog\Timeline\Timeline;
use WPVaaniLog\Settings\Settings;

defined( 'ABSPATH' ) || exit;

final class Menu {

	public function register_menus(): void {
		add_action( 'admin_menu', [ $this, 'register' ] );
	}


	/**
	 * Register plugin admin menus.
	*/
	public function register(): void {

		add_menu_page(
			__( 'WP VaaniLog', 'wp-vaanilog' ),
			__( 'Change Monitor', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-dashboard',
			[ new Dashboard(), 'render' ],
			'dashicons-backup',
			58
		);

		// ❌ DO NOT add Dashboard submenu.
		// WordPress automatically creates it.

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'Timeline', 'wp-vaanilog' ),
			__( 'Timeline', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-timeline',
			[ new Timeline(), 'render' ]
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'Settings', 'wp-vaanilog' ),
			__( 'Settings', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-settings',
			[ new Settings(), 'render' ]
		);

		add_submenu_page(
			'vaanilog-dashboard',
			__( 'About', 'wp-vaanilog' ),
			__( 'About', 'wp-vaanilog' ),
			'manage_options',
			'vaanilog-about',
			[ $this, 'about_page' ]
		);
	}

	/**
	 * Register hooks.
	*/
	public function about_page(): void {
		require VAANILOG_PLUGIN_DIR . 'includes/views/about.php';
	}
}