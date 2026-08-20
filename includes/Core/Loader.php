<?php

namespace WPVaaniLog\Core;

use WPVaaniLog\Admin\Menu;
use WPVaaniLog\Admin\Assets;
use WPVaaniLog\Database\Database;

defined( 'ABSPATH' ) || exit;

final class Loader {

	public function boot(): void {

		// Keep the table schema in sync with the running plugin version,
		// even on sites that update the plugin without ever deactivating
		// it. Cheap no-op once already up to date.
		Database::maybe_upgrade();

		// Logger must run everywhere (front-end login, cron-triggered
		// plugin updates, etc.), not just wp-admin. Database is the
		// concrete LoggerRepositoryInterface implementation, wired in
		// here - this is the plugin's single "composition point" for
		// Logger's dependency.
		( new Logger( new Database() ) )->register();

		// The retention cron must also run everywhere: it's triggered by
		// WP-Cron on a scheduled event, not tied to an admin page view.
		( new Cleanup() )->register();

		if ( ! is_admin() ) {
			return;
		}

		( new Menu() )->register_menus();
		( new Assets() )->register();
	}
}