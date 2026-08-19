<?php

namespace WPVaaniLog\Core;

defined( 'ABSPATH' ) || exit;

final class Deactivator {

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate(): void {

		flush_rewrite_rules();

	}

}