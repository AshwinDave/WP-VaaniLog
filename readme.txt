=== Vaanilog ===
Contributors: AshwinDave
Tags: monitoring, audit, changes, security, activity
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Monitor important WordPress changes in a clear, human-readable admin timeline.

== Description ==

Vaanilog records important changes made to your WordPress site, including post and page edits, user and role changes, plugin and theme changes, WordPress updates, and settings changes.

All logs are stored locally in your WordPress database.

== Installation ==

1. Upload the plugin through the WordPress Plugins screen.
2. Activate Vaanilog.
3. Open Change Monitor in the WordPress admin menu.

== Changelog ==

= 1.0.1 =
* Fixed: Event Details screen referenced a non-existent IP address field, causing a PHP warning on that screen.
* Added: Automatic daily cleanup of old log entries, with a configurable retention period (30/90/180/365 days, or forever) under Settings.
* Added: Database schema now stays in sync automatically after plugin updates, without needing to deactivate and reactivate.
* Hardened: A few admin-screen output values that were not explicitly escaped (all were static strings, not user input, so this was a defense-in-depth fix rather than a fix for an actual vulnerability).

= 1.0.0 =
* Initial release.


== Privacy ==

Vaanilog stores audit events in a custom database table on your WordPress site. It does not send logged data to an external service.

To reduce sensitive-data exposure, the plugin does not store raw post content in change snapshots and redacts common credential and token fields from tracked option values. Log entries are automatically deleted after 90 days by default; this retention period is configurable under Settings (30/90/180/365 days, or kept indefinitely). Administrators should review the site's tracking settings and retention practices before enabling monitoring on sensitive environments.
