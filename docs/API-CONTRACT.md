# cluuub embed API contract — decisions required

The implementation uses the existing HTTPS endpoint `GET /api/v1/public/{clubSlug}/showcase` on cluuub.app. The endpoint returns a JSON `data` object with public club groups, trainers, venues, board contacts, sponsors, news and visibility settings. WordPress calls it server-side and caches the response for five minutes. The plugin does not rely on the separate `public/embed.js` script and does not submit personal-data forms.

Before integration is considered production-ready, cluuub backend owners must define:

- Versioning policy for the existing showcase response.
- Confirm each included response field is intentionally public and scoped to the requested club.
- Scope news queries by club before enabling the news widget; the current service query appears unscoped.
- Confirm the `club` object in the response contains only public data.
- Validate the `allowedEmbedDomains` behavior for external client-side widgets.
- Error and unavailable states that do not reveal private data.
- Rate limits, caching semantics, monitoring, and availability expectations.
- Data processed, retention, processor/subprocessor details, and privacy notice URL.

The old static `public/embed.js` should be separately reviewed before use: it contains TypeScript-only casts despite being served as plain JavaScript, and its `innerHTML` templates include API-returned strings. The WordPress plugin currently avoids that script by using its own escaped server-side rendering path.
