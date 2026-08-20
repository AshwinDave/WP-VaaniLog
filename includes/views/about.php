<?php
/**
 * About view.
 *
 * @package WPVaaniLog
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap vaanilog-wrap">
	<h1 class="vaanilog-page-title">
		<span class="dashicons dashicons-info-outline"></span>
		<?php esc_html_e( 'About WP VaaniLog', 'wp-vaanilog' ); ?>
	</h1>

	<div class="vaanilog-about-card">
		<p>
			<?php esc_html_e( 'WP VaaniLog gives you a clean, human-readable timeline of important changes on your WordPress site, instead of a noisy raw activity log.', 'wp-vaanilog' ); ?>
		</p>

		<h2 class="vaanilog-section-title"><?php esc_html_e( 'Version', 'wp-vaanilog' ); ?></h2>
		<p><?php echo esc_html( VAANILOG_VERSION ); ?></p>

		<h2 class="vaanilog-section-title"><?php esc_html_e( 'What it tracks', 'wp-vaanilog' ); ?></h2>
		<ul class="vaanilog-about-list">
			<li><?php esc_html_e( 'User logins, role changes, and account creation', 'wp-vaanilog' ); ?></li>
			<li><?php esc_html_e( 'Post & page creation, updates, deletion, and restoration', 'wp-vaanilog' ); ?></li>
			<li><?php esc_html_e( 'Plugin installs, activations, updates, and deletions', 'wp-vaanilog' ); ?></li>
			<li><?php esc_html_e( 'Theme switches, updates, and deletions', 'wp-vaanilog' ); ?></li>
			<li><?php esc_html_e( 'WordPress core updates', 'wp-vaanilog' ); ?></li>
			<li><?php esc_html_e( 'Key site settings (URL, permalinks, reading & discussion settings)', 'wp-vaanilog' ); ?></li>
		</ul>

		<h2 class="vaanilog-section-title"><?php esc_html_e( 'Support', 'wp-vaanilog' ); ?></h2>
		<p><?php esc_html_e( 'For help and feature requests, please visit the plugin support forum on WordPress.org.', 'wp-vaanilog' ); ?></p>
	</div>
</div>
