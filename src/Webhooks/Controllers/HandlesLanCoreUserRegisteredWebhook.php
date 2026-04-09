<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\UserRegisteredPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

abstract class HandlesLanCoreUserRegisteredWebhook extends HandlesLanCoreWebhook
{
    protected function parsePayload(Request $request): WebhookPayload
    {
        return UserRegisteredPayload::fromRequest($request);
    }

    protected function handlePayload(WebhookPayload $payload): ?JsonResponse
    {
        /** @var UserRegisteredPayload $payload */
        $this->handle($payload);

        return null;
    }

    abstract protected function handle(UserRegisteredPayload $payload): void;
}
