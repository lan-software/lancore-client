<?php

use Illuminate\Support\Str;
use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use LanSoftware\LanCoreClient\LanCoreClient;
use LanSoftware\LanCoreClient\Testing\LanCoreClientFake;

it('provides a fake client that returns configured user', function () {
    $userId = (string) Str::ulid();

    $fake = LanCoreClientFake::create()
        ->withExchangeCode(new LanCoreUser(
            id: $userId,
            username: 'fakeuser',
            email: 'fake@test.com',
            roles: ['user'],
        ))
        ->bind();

    expect($fake)->toBeInstanceOf(LanCoreClient::class);

    $user = $fake->exchangeCode('any-code');

    expect($user)
        ->id->toBe($userId)
        ->username->toBe('fakeuser');
});

it('provides a fake client with user resolution', function () {
    $userId = (string) Str::ulid();

    $fake = LanCoreClientFake::create()
        ->withUser(['id' => $userId, 'username' => 'resolved', 'roles' => []])
        ->bind();

    $user = $fake->resolveUserById($userId);

    expect($user->username)->toBe('resolved');
});

it('asserts that API calls were sent', function () {
    LanCoreClientFake::create()
        ->withExchangeCode(['id' => (string) Str::ulid(), 'username' => 'u', 'roles' => []])
        ->bind()
        ->exchangeCode('code');

    LanCoreClientFake::assertSent('/api/integration/sso/exchange');
});
