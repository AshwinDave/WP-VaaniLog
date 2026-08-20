<?php
/**
 * Helper functions for WP VaaniLog.
 *
 * @package WPVaaniLog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single source of truth for default plugin settings. Both the
 * Settings screen and the Logger read this so the two can never drift
 * out of sync with each other (previously each hard-coded its own
 * copy of this array).
 *
 * @return array
 */
function vaanilog_default_settings(): array {
	return array(
		'track_users'        => 1,
		'track_plugins'      => 1,
		'track_themes'       => 1,
		'track_posts'        => 1,
		'track_settings'     => 1,
		// Days of history to keep before the daily cleanup cron prunes
		// old rows. 0 = keep forever.
		'log_retention_days' => 90,
	);
}

/**
 * Resolve a user_id (as stored on a log row) to a human-readable name.
 * Cached per-request since the same user shows up on many rows.
 *
 * @param int|null $user_id User ID, 0/null for system/guest.
 * @return string
 */
function vaanilog_get_username( $user_id ): string {

	static $cache = array();

	$user_id = (int) $user_id;

	if ( $user_id <= 0 ) {
		return __( 'System', 'wp-vaanilog' );
	}

	if ( isset( $cache[ $user_id ] ) ) {
		return $cache[ $user_id ];
	}

	$user = get_userdata( $user_id );

	$cache[ $user_id ] = $user ? $user->display_name : __( 'Deleted user', 'wp-vaanilog' );

	return $cache[ $user_id ];
}

/**
 * Get a readable plugin name from its file path.
 *
 * @param string $plugin_file e.g. 'akismet/akismet.php'.
 * @return string
 */
function vaanilog_get_plugin_name( $plugin_file ): string {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$all_plugins = get_plugins();

	if ( isset( $all_plugins[ $plugin_file ]['Name'] ) ) {
		return $all_plugins[ $plugin_file ]['Name'];
	}

	return $plugin_file;
}

/**
 * Map an event_type string to a human-readable label.
 *
 * @param string $event_type Event type key.
 * @return string
 */
function vaanilog_event_label( $event_type ): string {
	$labels = array(
		'post_created'               => __( 'Post Created', 'wp-vaanilog' ),
		'post_updated'                => __( 'Post Updated', 'wp-vaanilog' ),
		'post_deleted'                => __( 'Post Deleted', 'wp-vaanilog' ),
		'post_restored'               => __( 'Post Restored', 'wp-vaanilog' ),
		'page_created'                => __( 'Page Created', 'wp-vaanilog' ),
		'page_updated'                => __( 'Page Updated', 'wp-vaanilog' ),
		'page_deleted'                => __( 'Page Deleted', 'wp-vaanilog' ),
		'page_restored'               => __( 'Page Restored', 'wp-vaanilog' ),
		'user_created'                => __( 'User Created', 'wp-vaanilog' ),
		'user_created_admin'          => __( 'Administrator Created', 'wp-vaanilog' ),
		'user_deleted'                => __( 'User Deleted', 'wp-vaanilog' ),
		'user_role_changed'           => __( 'User Role Changed', 'wp-vaanilog' ),
		'user_role_changed_to_admin'  => __( 'User Promoted to Administrator', 'wp-vaanilog' ),
		'user_login'                  => __( 'User Logged In', 'wp-vaanilog' ),
		'user_logout'                 => __( 'User Logged Out', 'wp-vaanilog' ),
		'password_changed'            => __( 'Password Changed', 'wp-vaanilog' ),
		'plugin_activated'            => __( 'Plugin Activated', 'wp-vaanilog' ),
		'plugin_deactivated'          => __( 'Plugin Deactivated', 'wp-vaanilog' ),
		'plugin_updated'              => __( 'Plugin Updated', 'wp-vaanilog' ),
		'plugin_installed'            => __( 'Plugin Installed', 'wp-vaanilog' ),
		'plugin_deleted'              => __( 'Plugin Deleted', 'wp-vaanilog' ),
		'theme_switched'              => __( 'Theme Changed', 'wp-vaanilog' ),
		'theme_updated'               => __( 'Theme Updated', 'wp-vaanilog' ),
		'theme_installed'             => __( 'Theme Installed', 'wp-vaanilog' ),
		'theme_deleted'               => __( 'Theme Deleted', 'wp-vaanilog' ),
		'core_updated'                => __( 'WordPress Updated', 'wp-vaanilog' ),
	);

	if ( isset( $labels[ $event_type ] ) ) {
		return $labels[ $event_type ];
	}

	if ( 0 === strpos( $event_type, 'option_' ) ) {
		return __( 'Setting Changed', 'wp-vaanilog' );
	}

	return ucwords( str_replace( '_', ' ', $event_type ) );
}

/**
 * Get the icon (dashicon class) for a given object type.
 *
 * @param string $object_type Object type key.
 * @return string
 */
function vaanilog_event_icon( $object_type ): string {
	$icons = array(
		'post'    => 'dashicons-admin-post',
		'page'    => 'dashicons-admin-page',
		'user'    => 'dashicons-admin-users',
		'plugin'  => 'dashicons-admin-plugins',
		'theme'   => 'dashicons-admin-appearance',
		'core'    => 'dashicons-wordpress',
		'setting' => 'dashicons-admin-settings',
	);

	return isset( $icons[ $object_type ] ) ? $icons[ $object_type ] : 'dashicons-info';
}

/**
 * Format a timestamp into a friendly date/time pair.
 *
 * @param string $mysql_datetime MySQL datetime string.
 * @return array { 'date' => string, 'time' => string }
 */
function vaanilog_format_datetime( $mysql_datetime ): array {
	$timestamp = strtotime( $mysql_datetime );

	return array(
		'date' => date_i18n( get_option( 'date_format' ), $timestamp ),
		'time' => date_i18n( get_option( 'time_format' ), $timestamp ),
	);
}


/**
 * Determine whether a key or option name is likely to contain a secret.
 *
 * @param string $key Key/name to inspect.
 * @return bool
 */
function vaanilog_is_sensitive_key( $key ): bool {
	$key = strtolower( (string) $key );

	return (bool) preg_match(
		'/(^|[_\-.])(pass(word)?|secret|token|api[_\-.]?key|access[_\-.]?key|private[_\-.]?key|client[_\-.]?secret|auth(entication)?|credential(s)?|encryption[_\-.]?key|webhook[_\-.]?secret|license[_\-.]?key)([_\-.]|$)/i',
		$key
	);
}

/**
 * Redact credentials and other sensitive values before they are persisted.
 * This operates recursively so nested option arrays cannot leak secrets.
 *
 * @param mixed $value Value to redact.
 * @param int   $depth Recursion depth guard.
 * @return mixed
 */
function vaanilog_redact_sensitive_value( $value, int $depth = 0 ) {
	if ( $depth > 8 ) {
		return '[REDACTED]';
	}

	if ( is_array( $value ) ) {
		$redacted = array();

		foreach ( $value as $key => $item ) {
			if ( vaanilog_is_sensitive_key( (string) $key ) ) {
				$redacted[ $key ] = '[REDACTED]';
			} else {
				$redacted[ $key ] = vaanilog_redact_sensitive_value( $item, $depth + 1 );
			}
		}

		return $redacted;
	}

	if ( is_object( $value ) ) {
		return '[REDACTED OBJECT]';
	}

	if ( is_string( $value ) ) {
		// Redact common secret/token assignments embedded in serialized/text values.
		$value = preg_replace(
			'/((?:api[_\-.]?key|access[_\-.]?key|client[_\-.]?secret|password|passwd|secret|token|private[_\-.]?key|authorization)\s*[:=]\s*)(["\']?)[^"\'\s,;&]+/i',
			'$1$2[REDACTED]',
			$value
		);

		// Redact common bearer tokens and long JWT-like strings.
		$value = preg_replace( '/\bBearer\s+[A-Za-z0-9._~+\/=-]+/i', 'Bearer [REDACTED]', $value );
		$value = preg_replace( '/\beyJ[A-Za-z0-9_-]{20,}\.[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{10,}\b/', '[REDACTED TOKEN]', $value );

		return $value;
	}

	return $value;
}

/**
 * Create a privacy-safe post snapshot for change history.
 * Raw post content/excerpts are intentionally never persisted.
 *
 * @param \WP_Post $post Post object.
 * @return array
 */
function vaanilog_post_snapshot( \WP_Post $post ): array {
	return array(
		'title'        => $post->post_title,
		'status'       => $post->post_status,
		'slug'         => $post->post_name,
		'content_hash' => hash( 'sha256', (string) $post->post_content ),
		'excerpt_hash' => hash( 'sha256', (string) $post->post_excerpt ),
	);
}
