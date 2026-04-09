<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class IntegrationAccessedPayload extends WebhookPayload
{
    public function __construct(
        public int $integrationId,
        public int $lancoreUserId,
        public string $appSlug,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $integrationId = $request->integer('integration.id');
        $userId = $request->integer('integration.user_id');

        abort_unless($integrationId > 0 && $userId > 0, 422, 'Invalid payload.');

        return new static(
            integrationId: $integrationId,
            lancoreUserId: $userId,
            appSlug: (string) $request->input('integration.app_slug', ''),
        );
    }
}
