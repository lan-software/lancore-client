<?php

namespace LanSoftware\LanCoreClient\Entrance;

use Illuminate\Http\Client\PendingRequest;
use LanSoftware\LanCoreClient\LanCoreClient;

class EntranceClient
{
    public function __construct(
        private readonly LanCoreClient $client,
    ) {}

    /**
     * Validate a ticket token against LanCore's authoritative endpoint.
     *
     * @param  array<string, mixed>  $metadata  Audit metadata (operator_id, timestamp, etc.)
     * @return array<string, mixed>
     */
    public function validate(string $token, array $metadata = []): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->post('/api/entrance/validate', [
                'token' => $token,
                ...$metadata,
            ])
        )->json();
    }

    /**
     * Confirm a check-in after successful validation.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function confirmCheckin(string $token, string $validationId, array $metadata = []): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->post('/api/entrance/checkin', [
                'token' => $token,
                'validation_id' => $validationId,
                ...$metadata,
            ])
        )->json();
    }

    /**
     * Verify a check-in (e.g. re-check already checked-in ticket).
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function verifyCheckin(string $token, string $validationId, array $metadata = []): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->post('/api/entrance/verify-checkin', [
                'token' => $token,
                'validation_id' => $validationId,
                ...$metadata,
            ])
        )->json();
    }

    /**
     * Confirm an on-site payment during entrance.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function confirmPayment(string $token, string $validationId, string $paymentMethod, string $amount, array $metadata = []): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->post('/api/entrance/confirm-payment', [
                'token' => $token,
                'validation_id' => $validationId,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                ...$metadata,
            ])
        )->json();
    }

    /**
     * Submit a manual override (e.g. force check-in despite validation failure).
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function submitOverride(string $token, string $validationId, string $reason, array $metadata = []): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->post('/api/entrance/override', [
                'token' => $token,
                'validation_id' => $validationId,
                'reason' => $reason,
                ...$metadata,
            ])
        )->json();
    }

    /**
     * Search attendees by query string.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function searchAttendees(string $query, array $metadata = []): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->get('/api/entrance/search', [
                'q' => $query,
                ...$metadata,
            ])
        )->json();
    }

    /**
     * Fetch entrance analytics/stats from LanCore.
     *
     * @return array<string, mixed>
     */
    public function stats(?int $eventId = null): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->get('/api/entrance/stats', array_filter([
                'event_id' => $eventId,
            ]))
        )->json();
    }

    /**
     * Fetch available events from LanCore.
     *
     * @return array<int, array{id: int, name: string, start_date: string|null, end_date: string|null}>
     */
    public function events(): array
    {
        return $this->client->request(
            fn (PendingRequest $http) => $http->get('/api/entrance/events')
        )->json('events', []);
    }

    /**
     * Fetch the JWKS-style list of Ed25519 signing keys from LanCore.
     *
     * Results are cached using the configured cache store and TTL.
     *
     * @return array<int, array{kid: string, kty: string, crv: string, x: string}>
     */
    public function fetchSigningKeys(bool $forceRefresh = false): array
    {
        $cache = app('cache')->store(config('lancore.entrance.signing_keys_cache_store', 'file'));
        $cacheKey = 'lancore.jwks';
        $ttl = (int) config('lancore.entrance.signing_keys_cache_ttl', 3600);

        if (! $forceRefresh) {
            $cached = $cache->get($cacheKey);

            if (is_array($cached)) {
                return $cached;
            }
        }

        $endpoint = (string) config('lancore.entrance.signing_keys_endpoint', 'api/entrance/signing-keys');

        $response = $this->client->request(
            fn (PendingRequest $http) => $http->get($endpoint)
        );

        $keys = $response->json('keys');

        if (! is_array($keys)) {
            // Fall back to bootstrap keys if upstream response is malformed
            return $this->bootstrapKeys();
        }

        $keys = array_values(array_filter(
            $keys,
            fn ($k) => is_array($k) && isset($k['kid'], $k['x']),
        ));

        $cache->put($cacheKey, $keys, $ttl);

        return $keys;
    }

    /**
     * Parse bootstrap signing keys from config (comma-separated kid:x pairs).
     *
     * @return array<int, array{kid: string, x: string}>
     */
    private function bootstrapKeys(): array
    {
        $raw = (string) config('lancore.entrance.signing_keys_bootstrap', '');

        if ($raw === '') {
            return [];
        }

        $keys = [];

        foreach (explode(',', $raw) as $pair) {
            $parts = explode(':', trim($pair), 2);

            if (count($parts) === 2) {
                $keys[] = ['kid' => $parts[0], 'x' => $parts[1]];
            }
        }

        return $keys;
    }
}
