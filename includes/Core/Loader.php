<?php

namespace WPVaaniLog\Core;

use WPVaaniLog\Admin\Menu;
use WPVaaniLog\Admin\Assets;
use WPVaaniLog\Database\Database;

defined( 'ABSPATH' ) || exit;

final class Loader {

	public function boot(): void {

		// Logger must run everywhere (front-end login, cron-triggered
		// plugin updates, etc.), not just wp-admin. Database is the
		// concrete LoggerRepositoryInterface implementation, wired in
		// here - this is the plugin's single "composition point" for
		// Logger's dependency.
		( new Logger( new Database() ) )->register();

		if ( ! is_admin() ) {
			return;
		}

		( new Menu() )->register_menus();
		( new Assets() )->register();
	}
}