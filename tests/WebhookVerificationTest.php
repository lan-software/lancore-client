<?php

use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Middleware\VerifyLanCoreWebhook;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('accepts valid HMAC signature', function () {
    $body = '{"user":{"id":1,"roles":["admin"]}}';
    $secret = 'test-secret';
    $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

    $request = Request::create('/webhook', 'POST', content: $body);
    $request->headers->set('X-Webhook-Signature', $signature);
    $request->headers->set('X-Webhook-Event', 'user.roles_updated');

    $middleware = new VerifyLanCoreWebhook;
    $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), 'user.roles_updated');

    expect($response->getStatusCode())->toBe(200);
});

it('rejects invalid HMAC signature', function () {
    $body = '{"user":{"id":1,"roles":["admin"]}}';
    $signature = 'sha256=invalid';

    $request = Request::create('/webhook', 'POST', content: $body);
    $request->headers->set('X-Webhook-Signature', $signature);
    $request->headers->set('X-Webhook-Event', 'user.roles_updated');

    $middleware = new VerifyLanCoreWebhook;
    $middleware->handle($request, fn () => response()->json(['ok' => true]), 'user.roles_updated');
})->throws(HttpException::class);

it('rejects missing signature header', function () {
    $body = '{"user":{"id":1}}';

    $request = Request::create('/webhook', 'POST', content: $body);
    $request->headers->set('X-Webhook-Event', 'user.roles_updated');

    $middleware = new VerifyLanCoreWebhook;
    $middleware->handle($request, fn () => response()->json(['ok' => true]), 'user.roles_updated');
})->throws(HttpException::class);

it('rejects disallowed event header', function () {
    $body = '{"user":{"id":1}}';
    $secret = 'test-secret';
    $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

    $request = Request::create('/webhook', 'POST', content: $body);
    $request->headers->set('X-Webhook-Signature', $signature);
    $request->headers->set('X-Webhook-Event', 'announcement.published');

    $middleware = new VerifyLanCoreWebhook;
    $middleware->handle($request, fn () => response()->json(['ok' => true]), 'user.roles_updated');
})->throws(HttpException::class);

it('bypasses verification when secret is empty', function () {
    config(['lancore.webhooks.secret' => '']);

    $body = '{"user":{"id":1}}';

    $request = Request::create('/webhook', 'POST', content: $body);
    $request->headers->set('X-Webhook-Event', 'user.roles_updated');

    $middleware = new VerifyLanCoreWebhook;
    $response = $middleware->handle($request, fn () => response()->json(['ok' => true]), 'user.roles_updated');

    expect($response->getStatusCode())->toBe(200);
});
