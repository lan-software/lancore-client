<?php

use Illuminate\Support\Facades\Http;
use LanSoftware\LanCoreClient\Exceptions\LanCoreDisabledException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreUnavailableException;
use LanSoftware\LanCoreClient\LanCoreClient;

beforeEach(function () {
    config([
        'lancore.entrance.enabled' => true,
        'lancore.entrance.signing_keys_cache_store' => 'array',
        'lancore.entrance.signing_keys_cache_ttl' => 3600,
    ]);

    $this->client = app(LanCoreClient::class);
});

it('validates a ticket and returns decision', function () {
    Http::fake([
        '*/api/entrance/validate' => Http::response([
            'decision' => 'valid',
            'ticket_id' => 42,
        ]),
    ]);

    $result = $this->client->entrance()->validate('LCT1.kid.body.sig');

    expect($result)
        ->decision->toBe('valid')
        ->ticket_id->toBe(42);
});

it('throws LanCoreUnavailableException on 5xx from entrance', function () {
    Http::fake([
        '*/api/entrance/validate' => Http::response('Server Error', 503),
    ]);

    $this->client->entrance()->validate('token');
})->throws(LanCoreUnavailableException::class);

it('caches JWKS within TTL', function () {
    $callCount = 0;

    Http::fake([
        '*/api/entrance/signing-keys' => function () use (&$callCount) {
            $callCount++;

            return Http::response([
                'keys' => [['kid' => 'k1', 'kty' => 'OKP', 'crv' => 'Ed25519', 'x' => 'abc']],
            ]);
        },
    ]);

    $keys1 = $this->client->entrance()->fetchSigningKeys();
    $keys2 = $this->client->entrance()->fetchSigningKeys();

    expect($callCount)->toBe(1);
    expect($keys1)->toBe($keys2);
});

it('refetches JWKS when force refresh is requested', function () {
    $callCount = 0;

    Http::fake([
        '*/api/entrance/signing-keys' => function () use (&$callCount) {
            $callCount++;

            return Http::response([
                'keys' => [['kid' => 'k1', 'kty' => 'OKP', 'crv' => 'Ed25519', 'x' => 'abc']],
            ]);
        },
    ]);

    $this->client->entrance()->fetchSigningKeys();
    $this->client->entrance()->fetchSigningKeys(forceRefresh: true);

    expect($callCount)->toBe(2);
});

it('throws LanCoreDisabledException when entrance is not enabled', function () {
    config(['lancore.entrance.enabled' => false]);

    app(LanCoreClient::class)->entrance();
})->throws(LanCoreDisabledException::class);

it('fetches entrance stats', function () {
    Http::fake([
        '*/api/entrance/stats*' => Http::response([
            'total' => 150,
            'checked_in' => 42,
        ]),
    ]);

    $stats = $this->client->entrance()->stats(eventId: 1);

    expect($stats)->total->toBe(150);
});

it('fetches events list', function () {
    Http::fake([
        '*/api/entrance/events' => Http::response([
            'events' => [
                ['id' => 1, 'name' => 'LAN Party 2026'],
            ],
        ]),
    ]);

    $events = $this->client->entrance()->events();

    expect($events)->toHaveCount(1);
    expect($events[0]['name'])->toBe('LAN Party 2026');
});

it('searches attendees', function () {
    Http::fake([
        '*/api/entrance/search*' => Http::response([
            'results' => [['id' => 1, 'username' => 'found']],
        ]),
    ]);

    $result = $this->client->entrance()->searchAttendees('found');

    expect($result['results'])->toHaveCount(1);
});
