<?php

namespace WPVaaniLog\Core;

defined( 'ABSPATH' ) || exit;

use WPVaaniLog\Database\Database;

final class Activator {

	/**
	 * Plugin activation.
	 */
	public static function activate(): void {

		Database::install();

		update_option( 'vaanilog_db_version', VAANILOG_VERSION );

		Cleanup::schedule();

		flush_rewrite_rules();

	}

}