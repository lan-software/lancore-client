<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class IntegrationAccessedPayload extends WebhookPayload
{
    public function __construct(
        public string $integrationId,
        public string $lancoreUserId,
        public string $appSlug,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $integrationId = (string) $request->input('integration.id', '');
        $userId = (string) $request->input('integration.user_id', '');

        abort_unless(self::isUlid($integrationId) && self::isUlid($userId), 422, 'Invalid payload.');

        return new static(
            integrationId: $integrationId,
            lancoreUserId: $userId,
            appSlug: (string) $request->input('integration.app_slug', ''),
        );
    }
}
