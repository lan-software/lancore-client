# Changelog

All notable changes to `lan-software/lancore-client` are documented here.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.1.4] — 2026-04-25

### Changed
- Test suite now also runs against Pest 4 (`pestphp/pest` constraint widened
  to `^3.0 || ^4.0`). All 91 tests pass under both Pest 3 and Pest 4; the
  `arch()` rules introduced in v0.1.3 remain compatible with `pest-plugin-arch`
  v4.
- CI: bumped `ramsey/composer-install` from v3 to v4 and
  `codecov/codecov-action` from v5 to v6.

## [0.1.3] — 2026-04-25

### Added
- Comprehensive tests for all eight webhook payload classes (`UserRegistered`,
  `UserProfileUpdated`, `UserRolesUpdated`, `AnnouncementPublished`,
  `EventPublished`, `IntegrationAccessed`, `TicketPurchased`,
  `NewsArticlePublished`) covering happy-path parsing, validation aborts, and
  type-coercion edge cases via Pest datasets.
- Pest architecture (`arch`) tests pinning structural invariants: payloads
  extend `WebhookPayload` and are readonly, exceptions extend `LanCoreException`,
  webhook controllers live in their own namespace, no `dd`/`dump`/`var_dump`
  leaks into the production source tree.
- Default-config regression test pinning the package's `callback_url`,
  `base_url`, and webhook-secret defaults — guards against silent default
  drift like the one fixed in v0.1.2.
- Extended `VerifyLanCoreWebhook` middleware tests: timing-safe comparison
  (signatures of equal length but mismatched bytes), unsupported signature
  prefix, empty body with valid signature, multiple allowed events on the
  same route, case-sensitive event matching.
- `CHANGELOG.md` (this file).

## [0.1.2] — 2026-04-25

### Changed
- Default `callback_url` is now `${APP_URL}/auth/lancore/callback`
  (was `${APP_URL}/auth/callback`). Satellites that set `LANCORE_CALLBACK_URL`
  explicitly are unaffected. The new default aligns with the namespaced path
  used by every consuming satellite's `.env.example`.

## [0.1.1] — 2026

### Added
- Initial Packagist publish.

[Unreleased]: https://github.com/lan-software/lancore-client/compare/v0.1.4...HEAD
[0.1.4]: https://github.com/lan-software/lancore-client/compare/v0.1.3...v0.1.4
[0.1.3]: https://github.com/lan-software/lancore-client/compare/v0.1.2...v0.1.3
[0.1.2]: https://github.com/lan-software/lancore-client/compare/v0.1.1...v0.1.2
[0.1.1]: https://github.com/lan-software/lancore-client/releases/tag/v0.1.1
