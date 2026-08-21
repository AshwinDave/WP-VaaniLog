<?php
/**
 * Unit tests for includes/helpers.php.
 *
 * @package Vaanilog\Tests
 */

namespace Vaanilog\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class HelpersTest extends TestCase {

	/**
	 * @dataProvider sensitiveKeyProvider
	 */
	public function test_is_sensitive_key_detects_secret_like_keys( string $key, bool $expected ): void {
		$this->assertSame( $expected, vaanilog_is_sensitive_key( $key ) );
	}

	public static function sensitiveKeyProvider(): array {
		return array(
			'plain password'        => array( 'password', true ),
			'api key with dash'     => array( 'api-key', true ),
			'client secret'         => array( 'client_secret', true ),
			'auth token'            => array( 'auth_token', true ),
			'license key'           => array( 'license_key', true ),
			'unrelated option name' => array( 'blogname', false ),
			'unrelated timestamp'   => array( 'created_at', false ),
		);
	}

	public function test_redact_sensitive_value_redacts_flagged_array_keys(): void {
		$input = array(
			'username' => 'ashwin',
			'password' => 'super-secret',
			'nested'   => array(
				'api_key' => 'abc123',
				'label'   => 'keep me',
			),
		);

		$result = vaanilog_redact_sensitive_value( $input );

		$this->assertSame( 'ashwin', $result['username'] );
		$this->assertSame( '[REDACTED]', $result['password'] );
		$this->assertSame( '[REDACTED]', $result['nested']['api_key'] );
		$this->assertSame( 'keep me', $result['nested']['label'] );
	}

	public function test_redact_sensitive_value_redacts_embedded_bearer_token_in_string(): void {
		$value = vaanilog_redact_sensitive_value( 'Authorization: Bearer abc.def-ghi_123' );

		$this->assertStringContainsString( 'Bearer [REDACTED]', $value );
		$this->assertStringNotContainsString( 'abc.def-ghi_123', $value );
	}

	public function test_redact_sensitive_value_passes_through_plain_scalars(): void {
		$this->assertSame( 'hello world', vaanilog_redact_sensitive_value( 'hello world' ) );
		$this->assertSame( 42, vaanilog_redact_sensitive_value( 42 ) );
	}

	public function test_event_label_returns_mapped_label_for_known_type(): void {
		$this->assertSame( 'Post Updated', vaanilog_event_label( 'post_updated' ) );
	}

	public function test_event_label_falls_back_to_generic_setting_changed_for_option_prefix(): void {
		$this->assertSame( 'Setting Changed', vaanilog_event_label( 'option_blogname' ) );
	}

	public function test_event_label_humanizes_unknown_event_types(): void {
		$this->assertSame( 'Some Unmapped Thing', vaanilog_event_label( 'some_unmapped_thing' ) );
	}

	public function test_event_icon_returns_known_dashicon(): void {
		$this->assertSame( 'dashicons-admin-users', vaanilog_event_icon( 'user' ) );
	}

	public function test_event_icon_falls_back_to_info_icon_for_unknown_type(): void {
		$this->assertSame( 'dashicons-info', vaanilog_event_icon( 'something_unexpected' ) );
	}

	public function test_default_settings_has_all_tracking_keys_enabled_by_default(): void {
		$defaults = vaanilog_default_settings();

		foreach ( array( 'track_users', 'track_plugins', 'track_themes', 'track_posts', 'track_settings' ) as $key ) {
			$this->assertSame( 1, $defaults[ $key ], "Expected {$key} to default to enabled." );
		}
	}

	public function test_default_settings_retention_defaults_to_90_days(): void {
		$this->assertSame( 90, vaanilog_default_settings()['log_retention_days'] );
	}
}
