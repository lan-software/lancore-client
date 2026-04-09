<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\IntegrationAccessedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

abstract class HandlesLanCoreIntegrationAccessedWebhook extends HandlesLanCoreWebhook
{
    protected function parsePayload(Request $request): WebhookPayload
    {
        return IntegrationAccessedPayload::fromRequest($request);
    }

    protected function handlePayload(WebhookPayload $payload): ?JsonResponse
    {
        /** @var IntegrationAccessedPayload $payload */
        $this->handle($payload);

        return null;
    }

    abstract protected function handle(IntegrationAccessedPayload $payload): void;
}
