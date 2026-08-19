<?php
/**
 * Plugin Name: WP VaaniLog
 * Plugin URI: https://github.com/AshwinDave/wp-vaanilog
 * Description: Track important changes on your WordPress site with a clear, human-readable activity timeline.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Ashwin Dave
 * License: GPL v2 or later
 * Text Domain: wp-vaanilog
 */

defined( 'ABSPATH' ) || exit;

/*
|--------------------------------------------------------------------------
| Plugin Constants
|--------------------------------------------------------------------------
*/

define( 'VAANILOG_VERSION', '1.0.0' );
define( 'VAANILOG_PLUGIN_FILE', __FILE__ );
define( 'VAANILOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VAANILOG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VAANILOG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/*
|--------------------------------------------------------------------------
| Runtime autoloader
|--------------------------------------------------------------------------
| The plugin has no production Composer dependencies. Keep the distributed
| build self-contained so no development/vendor code is executed at runtime.
*/
spl_autoload_register(
	function ( $class ) {
		$prefix = 'WPVaaniLog\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$relative = substr( $class, strlen( $prefix ) );
		$file     = VAANILOG_PLUGIN_DIR . 'includes/' . str_replace( '\\', '/', $relative ) . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
);

require_once VAANILOG_PLUGIN_DIR . 'includes/helpers.php';

/*
|--------------------------------------------------------------------------
| Activation / Deactivation
|--------------------------------------------------------------------------
| These were previously never registered, so the log table was never
| actually created on activation and cleanup never ran on deactivation.
*/

register_activation_hook( __FILE__, array( 'WPVaaniLog\\Core\\Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WPVaaniLog\\Core\\Deactivator', 'deactivate' ) );

/*
|--------------------------------------------------------------------------
| Boot Plugin
|--------------------------------------------------------------------------
*/

WPVaaniLog\Core\Plugin::instance()->boot();