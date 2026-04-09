<?php

namespace LanSoftware\LanCoreClient\Webhooks\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLanCoreWebhook
{
    /**
     * @param  string  ...$allowedEvents  Event names this route accepts (e.g. 'user.roles_updated').
     */
    public function handle(Request $request, Closure $next, string ...$allowedEvents): Response
    {
        $event = $request->header('X-Webhook-Event');

        if ($allowedEvents !== [] && ! in_array($event, $allowedEvents, true)) {
            abort(400, 'Unsupported webhook event.');
        }

        $secret = (string) config('lancore.webhooks.secret', '');

        if ($secret !== '') {
            $signature = $request->header('X-Webhook-Signature');

            if (! is_string($signature) || ! str_starts_with($signature, 'sha256=')) {
                abort(401, 'Missing or malformed webhook signature.');
            }

            $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

            if (! hash_equals($expected, $signature)) {
                abort(401, 'Invalid webhook signature.');
            }
        }

        return $next($request);
    }
}
