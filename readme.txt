=== WP VaaniLog ===
Contributors: AshwinDave
Tags: monitoring, audit, changes, security, activity
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Monitor important WordPress changes in a clear, human-readable admin timeline.

== Description ==

WP VaaniLog records important changes made to your WordPress site, including post and page edits, user and role changes, plugin and theme changes, WordPress updates, and settings changes.

All logs are stored locally in your WordPress database.

== Installation ==

1. Upload the plugin through the WordPress Plugins screen.
2. Activate WP VaaniLog.
3. Open Change Monitor in the WordPress admin menu.

== Changelog ==

= 1.0.0 =
* Initial release.


== Privacy ==

WP VaaniLog stores audit events in a custom database table on your WordPress site. It does not send logged data to an external service.

To reduce sensitive-data exposure, the plugin does not store raw post content in change snapshots and redacts common credential and token fields from tracked option values. Administrators should review the site's tracking settings and retention practices before enabling monitoring on sensitive environments.
