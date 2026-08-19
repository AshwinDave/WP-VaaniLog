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

		flush_rewrite_rules();

	}

}