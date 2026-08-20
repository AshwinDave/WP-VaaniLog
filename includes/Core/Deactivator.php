<?php

namespace WPVaaniLog\Core;

defined( 'ABSPATH' ) || exit;

final class Deactivator {

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate(): void {

		Cleanup::unschedule();

		flush_rewrite_rules();

	}

}