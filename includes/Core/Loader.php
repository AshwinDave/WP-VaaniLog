<?php
/**
 * Boots the plugin components.
 *
 * @package Vaanilog
 */

namespace Vaanilog\Core;

use Vaanilog\Admin\Assets;
use Vaanilog\Admin\Menu;
use Vaanilog\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Registers and boots plugin services.
 */
final class Loader {

	/**
	 * Boot all plugin services.
	 *
	 * @return void
	 */
	public function boot(): void {
		Database::maybe_upgrade();

		( new Logger( new Database() ) )->register();
		( new Cleanup() )->register();

		if ( ! is_admin() ) {
			return;
		}

		( new Menu() )->register_menus();
		( new Assets() )->register();
	}
}
