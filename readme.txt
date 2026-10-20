=== cluuub Embed ===
Contributors: cluuub
Tags: cluuub, embed, clubs
Requires at least: 6.4
Requires PHP: 7.4
Stable tag: 0.2.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display public cluuub club information in WordPress.

== Description ==
This plugin adds a Gutenberg block and the [cluuub club="club-slug" type="angebote"] shortcode. Supported types: angebote, trainer, hallen, vorstand, sponsoren. Public data is requested server-side and cached for five minutes.

This plugin uses cluuub's public club showcase API. It does not process membership data or submit forms.

The optional cluuub credit link below widgets is disabled by default and is displayed only when a site administrator enables it in Settings > cluuub Embed.

The cluuub service provides the public club data displayed by this plugin. Read the [developer documentation](https://cluuub.app/developer), available in 14 languages, for setup and service details.

== Installation ==
1. Upload the cluuub-embed folder to /wp-content/plugins/.
2. Activate the plugin.
3. Configure the HTTPS cluuub address under Settings > cluuub Embed.
4. Add the cluuub block or [cluuub club="YOUR-CLUB-SLUG" type="angebote"] to a page.

== Frequently Asked Questions ==
= What data does it show? =
Only public showcase fields for offers, trainers, venues, board contacts and sponsors.

= Does it synchronize or store cluuub member data in WordPress? =
No. This version embeds a remote view and stores only plugin settings.

= Is a cluuub credit link shown on my website? =
No, not by default. A site administrator can explicitly enable the optional credit link in Settings > cluuub Embed.

== Privacy ==
On a cache miss, the WordPress server requests public club data from the configured cluuub HTTPS origin. cluuub receives the server's network address and requested club slug. Visitor browsers do not contact cluuub for these widgets. Site owners should disclose this server-to-server transfer in their privacy policy.

The plugin does not load third-party JavaScript, tracking pixels, or remote fonts. It makes a server-to-server HTTPS request only when a widget cache is empty or expired.

== Changelog ==
= 0.2.1 =
* Make the public cluuub credit link optional and disabled by default.
* Clarify external service requests and privacy behavior.

= 0.2.0 =
* Initial public showcase integration with Gutenberg block, shortcode, and five-minute cache.
