<?php
/**
 * PHPUnit bootstrap for WP VaaniLog unit tests.
 *
 * These are *unit* tests for pure-logic helpers (redaction, formatting,
 * label lookups) - they stub the handful of WordPress functions those
 * helpers call, rather than booting a full WordPress + MySQL test
 * environment. That keeps CI fast, dependency-free, and fast to run
 * locally with just `composer test`.
 *
 * If/when tests are added for code that talks to $wpdb, hooks, or the
 * database (Database.php, Logger.php, Search.php), those belong in the
 * WordPress core PHPUnit test suite (wp-env + WP_UnitTestCase) instead
 * of adding more stubs here - that's a separate, heavier test run.
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// includes/helpers.php exits immediately if ABSPATH isn't defined
// (the standard WordPress "no direct access" guard). Define it so the
// file under test loads normally here.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', sys_get_temp_dir() . '/' );
}

// --- Minimal WordPress function stubs used by includes/helpers.php ---
// Keep these intentionally dumb: they exist so pure logic can run
// outside WordPress, not to reimplement WordPress behaviour.

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES );
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	function date_i18n( $format, $timestamp = null ) {
		return date( $format, $timestamp ?? time() );
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $name, $default = false ) {
		$fallbacks = array(
			'date_format' => 'Y-m-d',
			'time_format' => 'H:i',
		);

		return $fallbacks[ $name ] ?? $default;
	}
}

require_once dirname( __DIR__ ) . '/includes/helpers.php';
