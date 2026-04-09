<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

/**
 * Base class for LanCore webhook controllers.
 *
 * Subclasses should use one of the event-specific abstract controllers
 * (e.g. HandlesLanCoreUserRolesUpdatedWebhook) rather than extending
 * this class directly.
 */
abstract class HandlesLanCoreWebhook
{
    abstract protected function parsePayload(Request $request): WebhookPayload;

    abstract protected function handlePayload(WebhookPayload $payload): ?JsonResponse;

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $this->parsePayload($request);

        return $this->handlePayload($payload) ?? response()->json(['status' => 'ok']);
    }
}
