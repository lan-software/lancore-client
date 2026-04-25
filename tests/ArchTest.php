<?php

/*
 * Pest architecture rules — pin structural invariants of the package source tree.
 * Architecture tests fail when someone violates the boundary by mistake; they catch
 * regressions that unit tests do not (e.g. a developer copy-pasting `dd()` into a
 * controller, or extending the wrong base class). Cheap to run, cheap to maintain.
 */

arch('every webhook payload extends WebhookPayload')
    ->expect('LanSoftware\LanCoreClient\Webhooks\Payloads')
    ->classes
    ->toExtend('LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload')
    ->ignoring('LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload');

arch('every webhook payload is readonly')
    ->expect('LanSoftware\LanCoreClient\Webhooks\Payloads')
    ->classes
    ->toBeReadonly()
    ->ignoring('LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload');

arch('the WebhookPayload base is abstract')
    ->expect('LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload')
    ->toBeAbstract();

arch('every package-defined exception extends LanCoreException')
    ->expect('LanSoftware\LanCoreClient\Exceptions')
    ->classes
    ->toExtend('LanSoftware\LanCoreClient\Exceptions\LanCoreException')
    ->ignoring('LanSoftware\LanCoreClient\Exceptions\LanCoreException');

arch('the LanCoreUser DTO is readonly')
    ->expect('LanSoftware\LanCoreClient\DTOs\LanCoreUser')
    ->toBeReadonly();

arch('production source never leaks debug helpers')
    ->expect(['dd', 'dump', 'var_dump', 'ray', 'print_r', 'var_export'])
    ->not->toBeUsed();

arch('webhook payloads do not depend on DTOs')
    ->expect('LanSoftware\LanCoreClient\Webhooks\Payloads')
    ->not->toUse('LanSoftware\LanCoreClient\DTOs');

arch('exceptions do not pull in HTTP, Eloquent, or webhook layers')
    ->expect('LanSoftware\LanCoreClient\Exceptions')
    ->not->toUse([
        'Illuminate\Http',
        'Illuminate\Database',
        'LanSoftware\LanCoreClient\Webhooks',
    ]);

arch('production code does not depend on the testing fake')
    ->expect([
        'LanSoftware\LanCoreClient\LanCoreClient',
        'LanSoftware\LanCoreClient\LanCoreServiceProvider',
        'LanSoftware\LanCoreClient\Entrance\EntranceClient',
        'LanSoftware\LanCoreClient\Webhooks',
        'LanSoftware\LanCoreClient\DTOs',
        'LanSoftware\LanCoreClient\Exceptions',
    ])
    ->not->toUse('LanSoftware\LanCoreClient\Testing');
