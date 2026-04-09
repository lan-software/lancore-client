<?php

use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use LanSoftware\LanCoreClient\Exceptions\InvalidLanCoreUserException;

it('creates from array with all fields', function () {
    $user = LanCoreUser::fromArray([
        'id' => 1,
        'username' => 'testuser',
        'email' => 'test@example.com',
        'locale' => 'de',
        'avatar_url' => 'https://example.com/a.png',
        'roles' => ['admin', 'user'],
        'created_at' => '2026-01-15T12:00:00Z',
    ]);

    expect($user)
        ->id->toBe(1)
        ->username->toBe('testuser')
        ->email->toBe('test@example.com')
        ->locale->toBe('de')
        ->avatar->toBe('https://example.com/a.png')
        ->roles->toBe(['admin', 'user'])
        ->createdAt->not->toBeNull();
});

it('creates from array with minimal fields', function () {
    $user = LanCoreUser::fromArray([
        'id' => 5,
        'username' => 'minimal',
    ]);

    expect($user)
        ->id->toBe(5)
        ->username->toBe('minimal')
        ->email->toBeNull()
        ->locale->toBeNull()
        ->avatar->toBeNull()
        ->roles->toBe([])
        ->createdAt->toBeNull();
});

it('prefers avatar_url over avatar key', function () {
    $user = LanCoreUser::fromArray([
        'id' => 1,
        'username' => 'u',
        'avatar' => 'fallback.png',
        'avatar_url' => 'preferred.png',
    ]);

    expect($user->avatar)->toBe('preferred.png');
});

it('falls back to avatar key when avatar_url missing', function () {
    $user = LanCoreUser::fromArray([
        'id' => 1,
        'username' => 'u',
        'avatar' => 'fallback.png',
    ]);

    expect($user->avatar)->toBe('fallback.png');
});

it('filters non-string roles', function () {
    $user = LanCoreUser::fromArray([
        'id' => 1,
        'username' => 'u',
        'roles' => ['admin', 123, null, 'user'],
    ]);

    expect($user->roles)->toBe(['admin', 'user']);
});

it('throws InvalidLanCoreUserException when id is missing', function () {
    LanCoreUser::fromArray(['username' => 'no-id']);
})->throws(InvalidLanCoreUserException::class);

it('throws InvalidLanCoreUserException when username is missing', function () {
    LanCoreUser::fromArray(['id' => 1]);
})->throws(InvalidLanCoreUserException::class);

it('converts to array', function () {
    $user = new LanCoreUser(
        id: 1,
        username: 'test',
        email: 'test@test.com',
        roles: ['user'],
    );

    $array = $user->toArray();

    expect($array)
        ->id->toBe(1)
        ->username->toBe('test')
        ->email->toBe('test@test.com')
        ->roles->toBe(['user']);
});
