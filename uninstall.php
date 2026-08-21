<?php
/**
 * Uninstall Vaanilog.
 *
 * @package Vaanilog
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/Core/Uninstaller.php';
Vaanilog\Core\Uninstaller::uninstall();
