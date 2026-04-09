<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class UserRegisteredPayload extends WebhookPayload
{
    public function __construct(
        public int $lancoreUserId,
        public string $username,
        public ?string $email,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $userId = $request->integer('user.id');

        abort_unless($userId > 0, 422, 'Invalid payload.');

        return new static(
            lancoreUserId: $userId,
            username: (string) $request->input('user.username', ''),
            email: $request->input('user.email'),
        );
    }
}
