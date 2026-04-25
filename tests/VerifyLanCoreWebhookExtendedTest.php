<?php

use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Middleware\VerifyLanCoreWebhook;
use Symfony\Component\HttpKernel\Exception\HttpException;

function webhookRequest(string $body, ?string $signature, ?string $event): Request
{
    $request = Request::create('/webhook', 'POST', content: $body);

    if ($signature !== null) {
        $request->headers->set('X-Webhook-Signature', $signature);
    }

    if ($event !== null) {
        $request->headers->set('X-Webhook-Event', $event);
    }

    return $request;
}

function passingNext(): Closure
{
    return fn () => response()->json(['ok' => true]);
}

it('uses a timing-safe comparison: a signature of the right length but wrong bytes is rejected', function () {
    config(['lancore.webhooks.secret' => 'real-secret']);

    $body = '{"user":{"id":1}}';
    // Same length as a valid sha256 signature but every byte wrong.
    $forged = 'sha256='.str_repeat('a', 64);

    (new VerifyLanCoreWebhook)->handle(
        webhookRequest($body, $forged, 'user.roles_updated'),
        passingNext(),
        'user.roles_updated',
    );
})->throws(HttpException::class, 'Invalid webhook signature.');

it('rejects a signature that does not start with sha256=', function () {
    config(['lancore.webhooks.secret' => 'real-secret']);

    $body = '{"user":{"id":1}}';
    // Same hex contents, but a different (unsupported) prefix.
    $bcrypt = 'bcrypt='.hash_hmac('sha256', $body, 'real-secret');

    (new VerifyLanCoreWebhook)->handle(
        webhookRequest($body, $bcrypt, 'user.roles_updated'),
        passingNext(),
        'user.roles_updated',
    );
})->throws(HttpException::class, 'Missing or malformed webhook signature.');

it('rejects when the event header is missing entirely (allowlist provided)', function () {
    config(['lancore.webhooks.secret' => 'real-secret']);

    (new VerifyLanCoreWebhook)->handle(
        webhookRequest('{}', 'sha256=irrelevant', null),
        passingNext(),
        'user.roles_updated',
    );
})->throws(HttpException::class, 'Unsupported webhook event.');

it('matches event names case-sensitively', function () {
    config(['lancore.webhooks.secret' => '']);
    $body = '{}';

    (new VerifyLanCoreWebhook)->handle(
        webhookRequest($body, null, 'User.Roles_Updated'),
        passingNext(),
        'user.roles_updated',
    );
})->throws(HttpException::class, 'Unsupported webhook event.');

it('accepts any of multiple allowed events on the same route', function () {
    config(['lancore.webhooks.secret' => '']);
    $middleware = new VerifyLanCoreWebhook;

    $first = $middleware->handle(
        webhookRequest('{}', null, 'announcement.published'),
        passingNext(),
        'announcement.published',
        'event.published',
    );

    $second = $middleware->handle(
        webhookRequest('{}', null, 'event.published'),
        passingNext(),
        'announcement.published',
        'event.published',
    );

    expect($first->getStatusCode())->toBe(200);
    expect($second->getStatusCode())->toBe(200);
});

it('verifies signatures correctly against an empty body', function () {
    config(['lancore.webhooks.secret' => 'empty-body-secret']);

    $body = '';
    $signature = 'sha256='.hash_hmac('sha256', $body, 'empty-body-secret');

    $response = (new VerifyLanCoreWebhook)->handle(
        webhookRequest($body, $signature, 'user.registered'),
        passingNext(),
        'user.registered',
    );

    expect($response->getStatusCode())->toBe(200);
});

it('skips event-allowlist enforcement when the route declares no allowed events', function () {
    config(['lancore.webhooks.secret' => '']);

    $response = (new VerifyLanCoreWebhook)->handle(
        webhookRequest('{}', null, 'anything.goes'),
        passingNext(),
    );

    expect($response->getStatusCode())->toBe(200);
});

it('rejects a sha256= signature whose hex segment is the wrong length', function () {
    config(['lancore.webhooks.secret' => 'real-secret']);

    // Truncated hex — same prefix, only 32 hex chars instead of 64.
    $truncated = 'sha256='.str_repeat('b', 32);

    (new VerifyLanCoreWebhook)->handle(
        webhookRequest('{}', $truncated, 'user.roles_updated'),
        passingNext(),
        'user.roles_updated',
    );
})->throws(HttpException::class, 'Invalid webhook signature.');
