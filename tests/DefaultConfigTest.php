<?php

/*
 * Pin the package's `config/lancore.php` defaults. v0.1.2 had to ship a fix
 * because the default `callback_url` drifted away from what every consuming
 * satellite documented. This file guards against the same class of regression.
 */

it('exposes the canonical /auth/lancore/callback default for callback_url', function () {
    $config = require __DIR__.'/../config/lancore.php';

    expect($config['callback_url'])
        ->toEndWith('/auth/lancore/callback');
});

it('keeps lancore.base_url defaulting to http://lancore.lan', function () {
    $config = require __DIR__.'/../config/lancore.php';

    expect($config['base_url'])->toBe('http://lancore.lan');
});

it('disables the integration by default so satellites must opt in', function () {
    $config = require __DIR__.'/../config/lancore.php';

    expect($config['enabled'])->toBeFalse();
});

it('ships an empty webhooks.secret so local-dev bypass is the default', function () {
    $config = require __DIR__.'/../config/lancore.php';

    expect($config['webhooks']['secret'])->toBe('');
});

it('exposes the entrance sub-client config block with safe defaults', function () {
    $config = require __DIR__.'/../config/lancore.php';

    expect($config['entrance'])
        ->toHaveKey('enabled')
        ->and($config['entrance']['enabled'])->toBeFalse()
        ->and($config['entrance']['signing_keys_endpoint'])->toBe('api/entrance/signing-keys');
});

it('keeps internal_url null by default so the client falls back to base_url', function () {
    $config = require __DIR__.'/../config/lancore.php';

    expect($config['internal_url'])->toBeNull();
});
