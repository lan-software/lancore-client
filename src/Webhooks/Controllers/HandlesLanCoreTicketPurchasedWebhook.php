<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\TicketPurchasedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

abstract class HandlesLanCoreTicketPurchasedWebhook extends HandlesLanCoreWebhook
{
    protected function parsePayload(Request $request): WebhookPayload
    {
        return TicketPurchasedPayload::fromRequest($request);
    }

    protected function handlePayload(WebhookPayload $payload): ?JsonResponse
    {
        /** @var TicketPurchasedPayload $payload */
        $this->handle($payload);

        return null;
    }

    abstract protected function handle(TicketPurchasedPayload $payload): void;
}
