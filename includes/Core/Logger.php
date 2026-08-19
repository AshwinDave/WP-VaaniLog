<?php
/**
 * Logger — listens to WordPress core hooks and writes rows into the
 * change-log table via an injected LoggerRepositoryInterface.
 *
 * This is the piece that was missing entirely in the previous version:
 * the plugin had a Dashboard/Timeline/Search that could only *read* the
 * log table, but nothing ever wrote to it.
 *
 * The repository is constructor-injected rather than called statically
 * so this class stays decoupled from the storage implementation and is
 * unit-testable with a fake repository.
 *
 * @package WP_Change_Monitor
 */

namespace WPVaaniLog\Core;

use WPVaaniLog\Database\LoggerRepositoryInterface;

defined( 'ABSPATH' ) || exit;

final class Logger {

	/**
	 * Storage backend the logger writes events to. Typed against the
	 * interface (not the concrete Database class) so this class never
	 * has to know *how* events are persisted, and so it can be unit
	 * tested by injecting a fake repository instead of a real $wpdb
	 * connection.
	 *
	 * @var LoggerRepositoryInterface
	 */
	private LoggerRepositoryInterface $db;

	/**
	 * @param LoggerRepositoryInterface $db Repository used to persist events.
	 */
	public function __construct( LoggerRepositoryInterface $db ) {
		$this->db = $db;
	}

	/**
	 * Option names that are noisy/internal and should never be logged,
	 * even when "Track Settings" is enabled.
	 *
	 * @var string[]
	 */
	private array $ignored_option_prefixes = array(
		'_transient_',
		'_site_transient_',
		'cron',
		'rewrite_rules',
		'auto_updater.lock',
		'db_upgraded',
		'recently_activated',
		'theme_mods_', // per-theme customizer/widget/menu data - noise on switch.
		'_vaanilog_', // any internal option this plugin itself may add later.
	);

	/**
	 * Exact option names to never log individually because they are
	 * already represented by a dedicated event (e.g. theme_switched
	 * covers 'template', 'stylesheet', 'current_theme', etc. all at
	 * once), or because they're purely internal WP bookkeeping.
	 *
	 * @var string[]
	 */
	private array $ignored_options = array(
		'template',
		'stylesheet',
		'current_theme',
		'sidebars_widgets',
		'theme_switched',
		'nav_menu_options',
		'widget_block',
	);

	/**
	 * Wire up every hook. Safe to call unconditionally on every request
	 * (front-end and admin) because change events (login, post save,
	 * plugin update, etc.) can happen outside wp-admin too.
	 */
	public function register(): void {

		// Posts & pages.
		add_action( 'transition_post_status', array( $this, 'on_transition_post_status' ), 10, 3 );
		add_action( 'post_updated', array( $this, 'on_post_updated' ), 10, 3 );
		add_action( 'before_delete_post', array( $this, 'on_before_delete_post' ) );

		// Users.
		add_action( 'user_register', array( $this, 'on_user_register' ) );
		add_action( 'delete_user', array( $this, 'on_delete_user' ) );
		add_action( 'set_user_role', array( $this, 'on_set_user_role' ), 10, 3 );
		add_action( 'wp_login', array( $this, 'on_wp_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'on_wp_logout' ) );
		add_action( 'password_reset', array( $this, 'on_password_reset' ) );

		// Plugins & themes.
		add_action( 'activated_plugin', array( $this, 'on_activated_plugin' ) );
		add_action( 'deactivated_plugin', array( $this, 'on_deactivated_plugin' ) );
		add_action( 'deleted_plugin', array( $this, 'on_deleted_plugin' ), 10, 2 );
		add_action( 'switch_theme', array( $this, 'on_switch_theme' ), 10, 3 );
		add_action( 'upgrader_process_complete', array( $this, 'on_upgrader_process_complete' ), 10, 2 );

		// Settings (options).
		add_action( 'updated_option', array( $this, 'on_updated_option' ), 10, 3 );
	}

	/**
	 * Whether a given tracking category is enabled in Settings.
	 * Defaults to "on" if settings have never been saved.
	 *
	 * @param string $key e.g. 'track_posts'.
	 */
	private function tracking_enabled( string $key ): bool {

		$settings = get_option(
			'vaanilog_settings',
			array(
				'track_users'    => 1,
				'track_plugins'  => 1,
				'track_themes'   => 1,
				'track_posts'    => 1,
				'track_settings' => 1,
			)
		);

		return ! empty( $settings[ $key ] );
	}

	/**
	 * Thin wrapper around the injected repository's insert_event() for
	 * readability at each call site below.
	 */
	private function log( array $data ): void {
		$this->db->insert_event( $data );
	}

	/*
	|--------------------------------------------------------------------
	| Posts & Pages
	|--------------------------------------------------------------------
	*/

	/**
	 * @param string   $new_status New post status.
	 * @param string   $old_status Previous post status.
	 * @param \WP_Post $post       Post object.
	 */
	public function on_transition_post_status( $new_status, $old_status, $post ): void {

		if ( ! $this->tracking_enabled( 'track_posts' ) ) {
			return;
		}

		if ( ! ( $post instanceof \WP_Post ) ) {
			return;
		}

		// Only track posts & pages; ignore revisions/autosaves/attachments/etc.
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		if ( wp_is_post_revision( $post ) || wp_is_post_autosave( $post ) ) {
			return;
		}

		// Skip the auto-draft placeholder WP creates before a post is
		// first saved - that's not a real edit yet.
		if ( 'auto-draft' === $old_status && 'auto-draft' === $new_status ) {
			return;
		}

		$object_type = 'page' === $post->post_type ? 'page' : 'post';

		if ( in_array( $old_status, array( 'new', 'auto-draft' ), true ) && ! in_array( $new_status, array( 'auto-draft', 'trash' ), true ) ) {
			$event = "{$object_type}_created";
		} elseif ( 'trash' === $new_status ) {
			$event = "{$object_type}_deleted"; // moved to trash.
		} elseif ( 'trash' === $old_status && 'trash' !== $new_status ) {
			$event = "{$object_type}_restored";
		} else {
			$event = "{$object_type}_updated";
		}

		$this->log(
			array(
				'event_type'  => $event,
				'object_type' => $object_type,
				'object_id'   => $post->ID,
				'object_name' => $post->post_title,
				'old_value'   => $old_status,
				'new_value'   => $new_status,
				'severity'    => 'trash' === $new_status ? 'critical' : 'normal',
			)
		);
	}

	/**
	 * Log meaningful edits to an existing post/page with a compact before/after snapshot.
	 *
	 * @param int      $post_id   Updated post ID.
	 * @param \WP_Post $post_after Post after the update.
	 * @param \WP_Post $post_before Post before the update.
	 */
	public function on_post_updated( $post_id, $post_after, $post_before ): void {

		if ( ! $this->tracking_enabled( 'track_posts' ) ) {
			return;
		}

		if ( ! ( $post_after instanceof \WP_Post ) || ! ( $post_before instanceof \WP_Post ) ) {
			return;
		}

		if ( ! in_array( $post_after->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		// Creation, deletion and restore are already represented by the
		// transition hook. This event is only for actual edits.
		if ( $post_after->post_status !== $post_before->post_status
			|| $post_after->post_title !== $post_before->post_title
			|| $post_after->post_content !== $post_before->post_content
			|| $post_after->post_excerpt !== $post_before->post_excerpt ) {

			// Store metadata and cryptographic fingerprints only. Raw post content
			// is intentionally not copied into the audit log because it may contain
			// personal, confidential, or otherwise sensitive information.
			$old_snapshot = vaanilog_post_snapshot( $post_before );
			$new_snapshot = vaanilog_post_snapshot( $post_after );

			$object_type = 'page' === $post_after->post_type ? 'page' : 'post';

			$this->log(
				array(
					'event_type'  => "{$object_type}_updated",
					'object_type' => $object_type,
					'object_id'   => $post_id,
					'object_name' => $post_after->post_title,
					'old_value'   => $old_snapshot,
					'new_value'   => $new_snapshot,
				)
			);
		}
	}

	/**
	 * Permanent deletion (bypasses trash, or trash emptied).
	 *
	 * @param int $post_id Post ID.
	 */
	public function on_before_delete_post( $post_id ): void {

		if ( ! $this->tracking_enabled( 'track_posts' ) ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post || ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		$object_type = 'page' === $post->post_type ? 'page' : 'post';

		$this->log(
			array(
				'event_type'  => "{$object_type}_deleted",
				'object_type' => $object_type,
				'object_id'   => $post->ID,
				'object_name' => $post->post_title,
				'severity'    => 'critical',
			)
		);
	}

	/*
	|--------------------------------------------------------------------
	| Users
	|--------------------------------------------------------------------
	*/

	/**
	 * @param int $user_id Newly registered user ID.
	 */
	public function on_user_register( $user_id ): void {

		if ( ! $this->tracking_enabled( 'track_users' ) ) {
			return;
		}

		$user     = get_userdata( $user_id );
		$is_admin = $user && in_array( 'administrator', (array) $user->roles, true );

		$this->log(
			array(
				'event_type'  => $is_admin ? 'user_created_admin' : 'user_created',
				'object_type' => 'user',
				'object_id'   => $user_id,
				'object_name' => $user ? $user->user_login : '',
				'severity'    => $is_admin ? 'critical' : 'normal',
			)
		);
	}

	/**
	 * @param int $user_id Deleted user ID.
	 */
	public function on_delete_user( $user_id ): void {

		if ( ! $this->tracking_enabled( 'track_users' ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		$this->log(
			array(
				'event_type'  => 'user_deleted',
				'object_type' => 'user',
				'object_id'   => $user_id,
				'object_name' => $user ? $user->user_login : '',
				'severity'    => 'critical',
			)
		);
	}

	/**
	 * @param int      $user_id   User whose role changed.
	 * @param string   $role      New role.
	 * @param string[] $old_roles Previous roles.
	 */
	public function on_set_user_role( $user_id, $role, $old_roles ): void {

		if ( ! $this->tracking_enabled( 'track_users' ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		$this->log(
			array(
				'event_type'  => 'administrator' === $role ? 'user_role_changed_to_admin' : 'user_role_changed',
				'object_type' => 'user',
				'object_id'   => $user_id,
				'object_name' => $user ? $user->user_login : '',
				'old_value'   => implode( ', ', (array) $old_roles ),
				'new_value'   => $role,
				'severity'    => 'administrator' === $role ? 'critical' : 'normal',
			)
		);
	}

	/**
	 * @param string   $user_login Username.
	 * @param \WP_User $user       User object.
	 */
	public function on_wp_login( $user_login, $user = null ): void {

		if ( ! $this->tracking_enabled( 'track_users' ) ) {
			return;
		}

		$this->log(
			array(
				'event_type'  => 'user_login',
				'object_type' => 'user',
				'object_id'   => $user ? $user->ID : 0,
				'object_name' => $user_login,
				'user_id'     => $user ? $user->ID : 0,
			)
		);
	}

	/**
	 * @param int $user_id User logging out.
	 */
	public function on_wp_logout( $user_id ): void {

		if ( ! $this->tracking_enabled( 'track_users' ) ) {
			return;
		}

		$user = get_userdata( $user_id );

		$this->log(
			array(
				'event_type'  => 'user_logout',
				'object_type' => 'user',
				'object_id'   => $user_id,
				'object_name' => $user ? $user->user_login : '',
				'user_id'     => $user_id,
			)
		);
	}

	/**
	 * @param \WP_User $user User whose password was reset.
	 */
	public function on_password_reset( $user ): void {

		if ( ! $this->tracking_enabled( 'track_users' ) ) {
			return;
		}

		$this->log(
			array(
				'event_type'  => 'password_changed',
				'object_type' => 'user',
				'object_id'   => $user ? $user->ID : 0,
				'object_name' => $user ? $user->user_login : '',
				'severity'    => 'critical',
			)
		);
	}

	/*
	|--------------------------------------------------------------------
	| Plugins & Themes
	|--------------------------------------------------------------------
	*/

	/**
	 * @param string $plugin Plugin basename.
	 */
	public function on_activated_plugin( $plugin ): void {

		if ( ! $this->tracking_enabled( 'track_plugins' ) ) {
			return;
		}

		$this->log(
			array(
				'event_type'  => 'plugin_activated',
				'object_type' => 'plugin',
				'object_name' => vaanilog_get_plugin_name( $plugin ),
			)
		);
	}

	/**
	 * @param string $plugin Plugin basename.
	 */
	public function on_deactivated_plugin( $plugin ): void {

		if ( ! $this->tracking_enabled( 'track_plugins' ) ) {
			return;
		}

		$this->log(
			array(
				'event_type'  => 'plugin_deactivated',
				'object_type' => 'plugin',
				'object_name' => vaanilog_get_plugin_name( $plugin ),
			)
		);
	}

	/**
	 * @param string $plugin_file Plugin basename.
	 * @param bool   $deleted     Whether deletion succeeded.
	 */
	public function on_deleted_plugin( $plugin_file, $deleted ): void {

		if ( ! $deleted || ! $this->tracking_enabled( 'track_plugins' ) ) {
			return;
		}

		$this->log(
			array(
				'event_type'  => 'plugin_deleted',
				'object_type' => 'plugin',
				'object_name' => $plugin_file,
				'severity'    => 'critical',
			)
		);
	}

	/**
	 * @param string    $new_name  New theme name.
	 * @param \WP_Theme $new_theme New theme object.
	 */
	public function on_switch_theme( $new_name, $new_theme = null ): void {

		if ( ! $this->tracking_enabled( 'track_themes' ) ) {
			return;
		}

		$this->log(
			array(
				'event_type'  => 'theme_switched',
				'object_type' => 'theme',
				'object_name' => $new_name,
			)
		);
	}

	/**
	 * Fires after plugin/theme/core updates via the WP Upgrader.
	 *
	 * @param \WP_Upgrader $upgrader   Upgrader instance.
	 * @param array        $hook_extra Extra info about what was upgraded.
	 */
	public function on_upgrader_process_complete( $upgrader, $hook_extra ): void {

		if ( empty( $hook_extra['type'] ) || ! in_array( $hook_extra['action'] ?? '', array( 'install', 'update' ), true ) ) {
			return;
		}

		if ( 'plugin' === $hook_extra['type'] && $this->tracking_enabled( 'track_plugins' ) ) {
			if ( 'install' === $hook_extra['action'] ) {
				// WordPress does not include the plugin basename in the install hook_extra.
				$this->log(
					array(
						'event_type'  => 'plugin_installed',
						'object_type' => 'plugin',
						'object_name' => __( 'Plugin package installed', 'wp-vaanilog' ),
					)
				);
			} else {
				foreach ( (array) ( $hook_extra['plugins'] ?? array() ) as $plugin_file ) {
					$this->log(
						array(
							'event_type'  => 'plugin_updated',
							'object_type' => 'plugin',
							'object_name' => vaanilog_get_plugin_name( $plugin_file ),
						)
					);
				}
			}
		} elseif ( 'theme' === $hook_extra['type'] && $this->tracking_enabled( 'track_themes' ) ) {
			if ( 'install' === $hook_extra['action'] ) {
				$this->log(
					array(
						'event_type'  => 'theme_installed',
						'object_type' => 'theme',
						'object_name' => __( 'Theme package installed', 'wp-vaanilog' ),
					)
				);
			} else {
				foreach ( (array) ( $hook_extra['themes'] ?? array() ) as $theme_slug ) {
					$this->log(
						array(
							'event_type'  => 'theme_updated',
							'object_type' => 'theme',
							'object_name' => $theme_slug,
						)
					);
				}
			}
		} elseif ( 'core' === $hook_extra['type'] ) {
			$this->log(
				array(
					'event_type'  => 'core_updated',
					'object_type' => 'core',
					'object_name' => 'WordPress',
					'new_value'   => get_bloginfo( 'version' ),
					'severity'    => 'critical',
				)
			);
		}
	}

	/*
	|--------------------------------------------------------------------
	| Settings / Options
	|--------------------------------------------------------------------
	*/

	/**
	 * @param string $option    Option name.
	 * @param mixed  $old_value Previous value.
	 * @param mixed  $new_value New value.
	 */
	public function on_updated_option( $option, $old_value, $new_value ): void {

		if ( ! $this->tracking_enabled( 'track_settings' ) ) {
			return;
		}

		// Never log the plugin's own settings save (avoids self-noise/loops)
		// or WordPress' internal/high-frequency options.
		if ( 'vaanilog_settings' === $option ) {
			return;
		}

		if ( in_array( $option, $this->ignored_options, true ) ) {
			return;
		}

		foreach ( $this->ignored_option_prefixes as $prefix ) {
			if ( 0 === strpos( $option, $prefix ) ) {
				return;
			}
		}

		// Never persist raw option values. Redact credential-like keys and
		// common token patterns recursively before the values reach storage.
		$safe_old = vaanilog_redact_sensitive_value( $old_value );
		$safe_new = vaanilog_redact_sensitive_value( $new_value );

		// If the option name itself looks credential-related, do not reveal its
		// name/value details in the audit trail.
		$display_name = vaanilog_is_sensitive_key( $option ) ? '[REDACTED OPTION]' : sanitize_text_field( $option );

		$this->log(
			array(
				'event_type'  => 'option_changed',
				'object_type' => 'setting',
				'object_name' => $display_name,
				'old_value'   => $safe_old,
				'new_value'   => $safe_new,
				'severity'    => vaanilog_is_sensitive_key( $option ) ? 'critical' : 'normal',
			)
		);
	}
}