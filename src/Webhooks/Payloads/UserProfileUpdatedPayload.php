<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class UserProfileUpdatedPayload extends WebhookPayload
{
    /**
     * @param  array<string, mixed>  $changes
     */
    public function __construct(
        public int $lancoreUserId,
        public array $changes,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $userId = $request->integer('user.id');

        abort_unless($userId > 0, 422, 'Invalid payload.');

        return new static(
            lancoreUserId: $userId,
            changes: (array) $request->input('user.changes', []),
        );
    }
}
