<?php

use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use LanSoftware\LanCoreClient\LanCoreClient;
use LanSoftware\LanCoreClient\Testing\LanCoreClientFake;

it('provides a fake client that returns configured user', function () {
    $fake = LanCoreClientFake::create()
        ->withExchangeCode(new LanCoreUser(
            id: 99,
            username: 'fakeuser',
            email: 'fake@test.com',
            roles: ['user'],
        ))
        ->bind();

    expect($fake)->toBeInstanceOf(LanCoreClient::class);

    $user = $fake->exchangeCode('any-code');

    expect($user)
        ->id->toBe(99)
        ->username->toBe('fakeuser');
});

it('provides a fake client with user resolution', function () {
    $fake = LanCoreClientFake::create()
        ->withUser(['id' => 5, 'username' => 'resolved', 'roles' => []])
        ->bind();

    $user = $fake->resolveUserById(5);

    expect($user->username)->toBe('resolved');
});

it('asserts that API calls were sent', function () {
    LanCoreClientFake::create()
        ->withExchangeCode(['id' => 1, 'username' => 'u', 'roles' => []])
        ->bind()
        ->exchangeCode('code');

    LanCoreClientFake::assertSent('/api/integration/sso/exchange');
});
