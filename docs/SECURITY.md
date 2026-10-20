# Security notes

- Only administrators with `manage_options` can access settings.
- Settings use WordPress Settings API and sanitization; output is escaped.
- The shortcode accepts a club slug and widget type from strict allowlists; values cannot select a host or arbitrary path.
- The API origin is HTTPS-only and configured by an administrator.
- Requests use `wp_safe_remote_get`, disable redirects, have a timeout, and cap the response at 1 MiB.
- Only the selected public fields are rendered, escaped as text; HTML from the API is never trusted.
- Responses are cached for five minutes. No personal-data write endpoint is called.
- The public widget is white-label by default; a cluuub credit link is added only when a site administrator explicitly enables the optional setting.
- Do not add authentication tokens, personal form submissions, or arbitrary remote script execution without a separate security review.
- Do not log tokens, member data, or full remote response bodies.

This is an initial implementation and still needs PHP/WordPress integration testing against a supported cluuub deployment. Review the public API response scope before release.
