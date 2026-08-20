<?php
/**
 * Main plugin service container.
 *
 * @package WPVaaniLog
 */

namespace WPVaaniLog\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Provides the plugin singleton and boot entry point.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * Plugin loader.
	 *
	 * @var Loader
	 */
	private Loader $loader;

	/**
	 * Create the plugin instance.
	 */
	private function __construct() {
		$this->loader = new Loader();
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Boot the plugin.
	 *
	 * @return void
	 */
	public function boot(): void {
		$this->loader->boot();
	}
}
