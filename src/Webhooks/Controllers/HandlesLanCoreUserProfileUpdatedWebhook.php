<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\UserProfileUpdatedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

abstract class HandlesLanCoreUserProfileUpdatedWebhook extends HandlesLanCoreWebhook
{
    protected function parsePayload(Request $request): WebhookPayload
    {
        return UserProfileUpdatedPayload::fromRequest($request);
    }

    protected function handlePayload(WebhookPayload $payload): ?JsonResponse
    {
        /** @var UserProfileUpdatedPayload $payload */
        $user = $this->resolveUser($payload->lancoreUserId);

        if ($user === null) {
            return response()->json(['status' => 'user_not_found'], 202);
        }

        $this->handle($user, $payload);

        return null;
    }

    abstract protected function resolveUser(int $lancoreUserId): ?Model;

    abstract protected function handle(Model $user, UserProfileUpdatedPayload $payload): void;
}
