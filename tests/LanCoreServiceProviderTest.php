<?php

use LanSoftware\LanCoreClient\LanCoreClient;
use LanSoftware\LanCoreClient\Testing\LanCoreClientFake;

/*
 * The service provider does three things that satellites depend on:
 *   - merges the package's config/lancore.php with the consumer's overrides
 *   - registers LanCoreClient as a scoped singleton
 *   - ships the testing fake under the same binding for swapping in tests
 *
 * These tests pin those contracts so a refactor that quietly drops a
 * `mergeConfigFrom` or changes the binding scope is caught here, not by a
 * downstream satellite reporting weird behaviour weeks later.
 */

it('binds LanCoreClient as a resolvable singleton', function () {
    $first = app(LanCoreClient::class);
    $second = app(LanCoreClient::class);

    expect($first)->toBeInstanceOf(LanCoreClient::class);
    expect($first)->toBe($second);
});

it('exposes lancore.* config keys after the provider boots', function () {
    expect(config('lancore.enabled'))->toBeTrue()
        ->and(config('lancore.base_url'))->toBe('http://lancore.test')
        ->and(config('lancore.token'))->toBe('test-token')
        ->and(config('lancore.app_slug'))->toBe('test-app');
});

it('preserves package defaults for keys the consumer does not set', function () {
    // The TestCase only sets a subset of `lancore.*`. Keys not set by the
    // consumer must come through from the package's config/lancore.php.
    expect(config('lancore.entrance.signing_keys_endpoint'))
        ->toBe('api/entrance/signing-keys');
});

it('lets a consumer swap the LanCoreClient binding for a fake', function () {
    $fake = LanCoreClientFake::create();

    app()->instance(LanCoreClient::class, $fake);

    expect(app(LanCoreClient::class))->toBe($fake);
});
