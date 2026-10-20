# cluuub Embed for WordPress

Open-source WordPress integration for cluuub's public club showcase endpoint. It provides a Gutenberg block and `[cluuub club="club-slug" type="angebote"]` shortcode for public offers, trainers, venues, board contacts and sponsors.

## Current boundary

The plugin calls `GET /api/v1/public/{clubSlug}/showcase` server-side and caches the response for five minutes. It only renders the selected public fields, escaping them as text. It does not copy member data into WordPress or post data to cluuub. News are not yet exposed because the current backend service needs a club-scope check first.

## Setup

Configure the HTTPS cluuub origin under **Settings → cluuub Embed**. Add the Gutenberg block or shortcode to a page. On cache miss, the WordPress server fetches public club data from cluuub; cluuub receives the WordPress server's network address and requested club slug. Visitors' browsers do not contact cluuub for this widget display.

## Development

Run `npm test` for static safety checks. PHP is needed to run WordPress/PHP compatibility checks. See `docs/PRODUCT-SCOPE.md` and `docs/API-CONTRACT.md` before adding features.

## License

GPL-2.0-or-later. See `LICENSE`.
