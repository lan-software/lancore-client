<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class UserProfileUpdatedPayload extends WebhookPayload
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public string $lancoreUserId,
        public array $changes,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $userId = (string) $request->input('user.id', '');

        abort_unless(self::isUlid($userId), 422, 'Invalid payload.');

        return new static(
            lancoreUserId: $userId,
            changes: (array) $request->input('user.changes', []),
        );
    }
}
