lancore-client
===

The shared Composer package that every Lan* satellite application uses to
talk to LanCore — HTTP transport, SSO authorization-code flow, webhook
signature verification, and (opt-in) JWKS-backed ticket-validation.

## Environment contract

Every satellite reads the same set of environment variables (defaults in
`config/lancore.php`):

| Variable                       | Purpose                                               |
|--------------------------------|-------------------------------------------------------|
| `LANCORE_ENABLED`              | Master kill-switch (`true` / `false`)                 |
| `LANCORE_BASE_URL`             | Browser-facing LanCore URL (SSO redirects)            |
| `LANCORE_INTERNAL_URL`         | Server-to-server URL; falls back to `LANCORE_BASE_URL`|
| `LANCORE_TOKEN`                | Bearer token minted by LanCore                        |
| `LANCORE_APP_SLUG`             | Satellite identity (e.g. `lanbrackets`)               |
| `LANCORE_CALLBACK_URL`         | OAuth callback URL registered on LanCore              |
| `LANCORE_WEBHOOK_SECRET`       | HMAC-SHA256 key for incoming-webhook verification     |
| `LANCORE_ENTRANCE_ENABLED`     | LanEntrance-only opt-in for the JWKS sub-client       |
| `LANCORE_SIGNING_KEYS_ENDPOINT`| JWKS endpoint URL                                     |
| `LANCORE_SIGNING_KEYS_CACHE_TTL` | JWKS cache TTL in seconds                           |

These env vars are stable across provisioning paths — the package itself
does not care how they were populated.

## Declarative provisioning via LanCore config

When LanCore is deployed with the [`lan-software` Helm umbrella chart](https://github.com/lan-software/LanChart),
every satellite's `LANCORE_TOKEN` and `LANCORE_WEBHOOK_SECRET` are provisioned
automatically from a **shared seed Secret** the umbrella emits
(`<release>-integrations-seed`). The umbrella chart:

1. Auto-generates a per-slug token + webhook secrets (via Helm `lookup`,
   stable across upgrades), OR honours operator overrides in
   `global.integrations.<slug>.{token,announcementWebhookSecret,rolesWebhookSecret}`.
2. Mounts the full seed Secret into LanCore so `config/integrations.php`
   can read each slug's `<SLUG>_LANCORE_TOKEN` env var via `env()`.
3. Mounts each satellite's slice of the same Secret — LanCore's
   `<SLUG>_LANCORE_TOKEN` becomes the satellite's `LANCORE_TOKEN`, and
   `<SLUG>_ROLES_WEBHOOK_SECRET` becomes its `LANCORE_WEBHOOK_SECRET`.
4. Runs `php artisan integrations:sync` as a pre-install/pre-upgrade Helm
   hook Job against LanCore, which reconciles `config/integrations.php`
   into the database — creating or updating each `IntegrationApp` row,
   seeding the config-seeded token (SHA-256-hashed), and refreshing the
   subscribed `Webhook` rows.

**Operator effect:** `helm install lan-software` produces a working fleet
with no admin-UI clickthrough, no `kubectl exec`, no per-satellite token
paste. Hostnames derive from `global.domain` + `global.satelliteHostStyle`
(flat / prefixed / custom) so the whole fleet is hostname-agile.

See:
- [LanChart `docs/adr/0008-declarative-integration-config.md`](https://github.com/lan-software/LanChart/blob/main/docs/adr/0008-declarative-integration-config.md)
- [LanCore MIL-STD-498 SSDD §5.4.5](https://github.com/lan-software/LanCore/blob/main/docs/mil-std-498/SSDD.md#545-integration-declarative-config-reconciler)
- [LanCore MIL-STD-498 IRS §3.5a IF-INTCFG](https://github.com/lan-software/LanCore/blob/main/docs/mil-std-498/IRS.md#35a-integration-declarative-configuration-if-intcfg)

## Local development (Docker Compose / Sail)

For local dev without the Helm chart, set the env vars directly in the
satellite's `.env` file. LanCore ships an `integration:setup-dev <slug>`
Artisan command that mints a dev token and prints the `.env` snippet to
copy. Alternatively, run `php artisan integrations:sync` against LanCore
with `LANCORE_INTEGRATIONS_RECONCILE_ON_BOOT=true` to pick up
`config/integrations.php` at LanCore boot.

## Using the client from a satellite

```php
use LanSoftware\LanCoreClient\LanCoreClient;

$client = app(LanCoreClient::class);

// Resolve a user by LanCore id:
$user = $client->user($lancoreUserId);

// Exchange an SSO code:
$user = $client->exchangeCode($authorizationCode);

// (LanEntrance only) Validate a signed ticket token:
$result = $client->entrance()->validate($plainToken);
```

See the package source under `src/` + tests under `tests/` for the full
API surface.
