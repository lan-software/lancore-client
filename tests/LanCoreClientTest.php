<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use LanSoftware\LanCoreClient\Exceptions\InvalidLanCoreUserException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreDisabledException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreRequestException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreUnavailableException;
use LanSoftware\LanCoreClient\LanCoreClient;

beforeEach(function () {
    $this->client = app(LanCoreClient::class);
});

it('builds SSO authorize URL with app slug and callback', function () {
    $url = $this->client->ssoAuthorizeUrl();

    expect($url)
        ->toContain('http://lancore.test/sso/authorize')
        ->toContain('app=test-app')
        ->toContain('redirect_uri=');
});

it('exchanges code and returns LanCoreUser DTO', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => [
                'id' => 42,
                'username' => 'testuser',
                'email' => 'test@example.com',
                'locale' => 'en',
                'avatar_url' => 'https://example.com/avatar.png',
                'roles' => ['admin', 'user'],
                'created_at' => '2026-01-01T00:00:00Z',
            ],
        ]),
    ]);

    $user = $this->client->exchangeCode('valid-code');

    expect($user)
        ->toBeInstanceOf(LanCoreUser::class)
        ->id->toBe(42)
        ->username->toBe('testuser')
        ->email->toBe('test@example.com')
        ->locale->toBe('en')
        ->avatar->toBe('https://example.com/avatar.png')
        ->roles->toBe(['admin', 'user']);
});

it('throws InvalidLanCoreUserException on malformed payload', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => ['username' => 'no-id'],
        ]),
    ]);

    $this->client->exchangeCode('code');
})->throws(InvalidLanCoreUserException::class);

it('throws LanCoreRequestException on 4xx', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'error' => 'Invalid code',
        ], 400),
    ]);

    $this->client->exchangeCode('bad-code');
})->throws(LanCoreRequestException::class, 'Invalid code');

it('throws LanCoreUnavailableException on 5xx', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response('Server Error', 503),
    ]);

    $this->client->exchangeCode('code');
})->throws(LanCoreUnavailableException::class);

it('throws LanCoreUnavailableException on connection failure', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => fn () => throw new ConnectionException('Connection refused'),
    ]);

    config(['lancore.http.retries' => 0]);

    $this->client->exchangeCode('code');
})->throws(LanCoreUnavailableException::class);

it('throws LanCoreDisabledException when disabled', function () {
    config(['lancore.enabled' => false]);

    app(LanCoreClient::class)->exchangeCode('code');
})->throws(LanCoreDisabledException::class);

it('sends Bearer token from config', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => ['id' => 1, 'username' => 'u', 'roles' => []],
        ]),
    ]);

    $this->client->exchangeCode('code');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'Bearer test-token');
    });
});

it('configures retry and timeout from config', function () {
    Http::fake([
        '*/api/integration/sso/exchange' => Http::response([
            'data' => ['id' => 1, 'username' => 'u', 'roles' => []],
        ]),
    ]);

    config([
        'lancore.http.retries' => 5,
        'lancore.http.retry_delay' => 200,
        'lancore.http.timeout' => 10,
    ]);

    $user = app(LanCoreClient::class)->exchangeCode('code');

    expect($user)->toBeInstanceOf(LanCoreUser::class);

    Http::assertSentCount(1);
});

it('resolves user by id', function () {
    Http::fake([
        '*/api/integration/user/resolve' => Http::response([
            'data' => ['id' => 42, 'username' => 'found', 'roles' => ['user']],
        ]),
    ]);

    $user = $this->client->resolveUserById(42);

    expect($user->id)->toBe(42);

    Http::assertSent(fn ($request) => $request['user_id'] === 42);
});

it('resolves user by email', function () {
    Http::fake([
        '*/api/integration/user/resolve' => Http::response([
            'data' => ['id' => 7, 'username' => 'emailuser', 'email' => 'a@b.com', 'roles' => []],
        ]),
    ]);

    $user = $this->client->resolveUserByEmail('a@b.com');

    expect($user->email)->toBe('a@b.com');

    Http::assertSent(fn ($request) => $request['email'] === 'a@b.com');
});

it('fetches current user', function () {
    Http::fake([
        '*/api/integration/user/me' => Http::response([
            'data' => ['id' => 1, 'username' => 'me', 'roles' => ['admin']],
        ]),
    ]);

    $user = $this->client->currentUser();

    expect($user->username)->toBe('me');
});
