# WordPress setup guide

This guide covers the current cluuub Embed plugin release. The public API documentation is available from [cluuub's developer documentation](https://cluuub.app/developer), which supports 14 languages.

## Requirements

- WordPress 6.4 or later;
- PHP 7.4 or later;
- outbound HTTPS requests from the WordPress host to the configured cluuub environment.

These are the plugin's declared minimums. Automated smoke checks do not replace an end-to-end test on your specific hosting, theme, and page builder.

## Install

1. Download the installable ZIP from [GitHub Releases](https://github.com/duphub42/cluuub-wordpress/releases/latest). Preview builds are available from **Code → Download ZIP**.
2. In WordPress, open **Plugins → Add New Plugin → Upload Plugin**.
3. Select the ZIP, install it, and activate **cluuub Embed**.
4. Open **Settings → cluuub Embed**. The default service origin is `https://cluuub.app`.
5. Add the Gutenberg block or a shortcode to a page and preview it before publishing.

## Find the club slug

The slug is the short public identifier of your club. Enter the slug only, not the entire URL. Ask your cluuub club administrator if you do not know which slug is public. The examples below use `sample-club` as sample text.

## Gutenberg block

Search the block inserter for **cluuub Embed**. Set the club slug, one of the five content types, the maximum number of entries, and an optional text filter. The editor placeholder shows the selected settings; public content is fetched when the page is rendered.

## Shortcodes

```text
[cluuub club="sample-club" type="angebote"]
[cluuub club="sample-club" type="trainer" limit="8" filter="youth"]
[cluuub club="sample-club" type="hallen"]
[cluuub club="sample-club" type="vorstand"]
[cluuub club="sample-club" type="sponsoren"]
```

Supported types are `angebote`, `trainer`, `hallen`, `vorstand`, and `sponsoren`. The `limit` attribute accepts 1–50 entries. `filter` is optional and searches the text fields shown for the selected type.

## Appearance

The plugin adds the CSS classes `.cluuub-widget`, `.cluuub-grid`, and `.cluuub-card`. Add theme overrides under **Appearance → Customize → Additional CSS** or in your child theme. For example:

```css
.cluuub-widget h2 { color: #163a70; }
.cluuub-widget .cluuub-card {
  border-radius: 1rem;
  box-shadow: 0 0.25rem 1rem rgb(15 23 42 / 10%);
}
```

Test the page on desktop and mobile because a theme or page builder may apply its own CSS.

## Troubleshooting

- **No items appear:** Confirm the slug and type. Check that the club has published public entries and visibility is enabled for that content type.
- **The content is stale:** Responses are cached for five minutes. Wait for the cache to expire, then reload.
- **The remote service cannot be reached:** Confirm the origin uses HTTPS and ask your host whether outbound requests to the service are allowed.
- **The layout differs from the editor placeholder:** The placeholder is not a live data preview. Check theme CSS and view the published page at mobile and desktop widths.
- **The page is empty but the shortcode is valid:** For visitors, remote service errors intentionally do not expose technical details. An administrator can review the shortcode and origin settings.

## Privacy

On a cache miss, the WordPress server sends the configured cluuub service an HTTPS request containing the requested club slug. The service receives the WordPress host's network address. The response is cached by WordPress for up to five minutes. The visitor's browser does not request widget data directly from cluuub. See [Security and privacy](SECURITY.md).

## Compatibility status

The plugin declares WordPress 6.4+ and PHP 7.4+. The repository has static checks and a PHP smoke test, but no completed end-to-end compatibility matrix for WordPress versions, themes, multisite, or third-party page builders. Report the WordPress/PHP version, theme, and relevant error when requesting help.
