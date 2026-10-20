# Product scope and roadmap

## Product goal

Let clubs publish selected public cluuub information on their websites with minimal technical effort. Provide a WordPress-native block and shortcode while keeping cluuub business rules and data visibility on the platform.

## MVP

- Configure one HTTPS cluuub environment.
- Render public club programs, trainers, venues, board contacts and sponsors using the existing public showcase endpoint.
- Gutenberg block and shortcode with club slug, widget type, item limit and text filter.
- Server-side WordPress request with a five-minute cache; visitor browsers do not contact cluuub for widget display.
- Admin-only configuration, validated settings, responsive card layout, and safe text escaping.
- UI strings use WordPress translation functions and are ready for translation.
- Clear privacy disclosure and useful admin-facing service errors.
- CI checks, versioned releases, changelog and uninstall behavior.

## Next releases

1. Copyable CMS-neutral embed snippet from cluuub admin, with consent and safe script behavior.
2. Trial request and court booking actions only after privacy, abuse controls and accessible form flows are reviewed.
3. News widget after backend response is restricted to the requested club.
4. Admin connection test, manual cache purge and additional appearance settings.
5. Translation catalogs and further locales.

## Explicitly out of initial scope

- Bidirectional sync or local copy of member data.
- Credential collection in WordPress.
- Arbitrary remote domains or arbitrary HTML/JS.
- Analytics/tracking without an explicit product and consent decision.
- Public release until API, security, licensing of included assets, and compatibility have been reviewed.

## Acceptance criteria for MVP

- Invalid club slugs and widget types produce no remote request.
- Only HTTPS origins are accepted; shortcode values cannot select arbitrary hosts.
- Only approved public showcase data is requested, capped to a small response and cached briefly.
- Settings require `manage_options` and use WordPress Settings API sanitization.
- Output is escaped as text and no user-submitted write action is exposed.
- Privacy documentation states the server-side request and host IP disclosure.
- Compatibility/security checks pass in CI and a real WordPress test site.
