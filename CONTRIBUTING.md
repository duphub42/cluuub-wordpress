# Contributing

Thanks for helping improve cluuub Embed for WordPress.

## Before opening a change

- Check the current [product scope](docs/PRODUCT-SCOPE.md) and [API contract](docs/API-CONTRACT.md).
- Keep integrations read-only unless a separately reviewed product change explicitly adds a write flow.
- Escape all remote values before rendering; do not add arbitrary remote scripts or hosts.
- Add or update documentation when shortcode behavior, setup, privacy, or requirements change.

## Run checks

```bash
npm test
find . -name '*.php' -print0 | xargs -0 -n1 php -l
php tests/php-smoke.php
```

Open a pull request with a concise description, user-visible impact, and test results. Do not include real member data, credentials, or production responses in test fixtures.
