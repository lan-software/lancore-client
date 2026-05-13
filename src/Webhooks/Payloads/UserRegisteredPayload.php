<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class UserRegisteredPayload extends WebhookPayload
{
    public function __construct(
        public string $lancoreUserId,
        public string $username,
        public ?string $email,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $userId = (string) $request->input('user.id', '');

        abort_unless(self::isUlid($userId), 422, 'Invalid payload.');

        return new static(
            lancoreUserId: $userId,
            username: (string) $request->input('user.username', ''),
            email: $request->input('user.email'),
        );
    }
}
