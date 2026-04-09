<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\AnnouncementPublishedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

abstract class HandlesLanCoreAnnouncementPublishedWebhook extends HandlesLanCoreWebhook
{
    protected function parsePayload(Request $request): WebhookPayload
    {
        return AnnouncementPublishedPayload::fromRequest($request);
    }

    protected function handlePayload(WebhookPayload $payload): ?JsonResponse
    {
        /** @var AnnouncementPublishedPayload $payload */
        $this->handle($payload);

        return null;
    }

    abstract protected function handle(AnnouncementPublishedPayload $payload): void;
}
