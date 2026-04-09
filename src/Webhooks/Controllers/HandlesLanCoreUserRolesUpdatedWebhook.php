<?php

namespace LanSoftware\LanCoreClient\Webhooks\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\UserRolesUpdatedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;

abstract class HandlesLanCoreUserRolesUpdatedWebhook extends HandlesLanCoreWebhook
{
    protected function parsePayload(Request $request): WebhookPayload
    {
        return UserRolesUpdatedPayload::fromRequest($request);
    }

    protected function handlePayload(WebhookPayload $payload): ?JsonResponse
    {
        /** @var UserRolesUpdatedPayload $payload */
        $user = $this->resolveUser($payload->lancoreUserId);

        if ($user === null) {
            return response()->json(['status' => 'user_not_found'], 202);
        }

        $this->syncRoles($user, $payload);

        return null;
    }

    /**
     * Resolve a local user model from a LanCore user ID.
     * Return null if the user does not exist locally.
     */
    abstract protected function resolveUser(int $lancoreUserId): ?Model;

    /**
     * Synchronize the user's roles based on the webhook payload.
     */
    abstract protected function syncRoles(Model $user, UserRolesUpdatedPayload $payload): void;
}
