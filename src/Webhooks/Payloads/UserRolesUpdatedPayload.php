<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class UserRolesUpdatedPayload extends WebhookPayload
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public string $lancoreUserId,
        public array $roles,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $userId = (string) $request->input('user.id', '');
        $roles = $request->input('user.roles');

        abort_unless(self::isUlid($userId) && is_array($roles), 422, 'Invalid payload.');

        return new static(
            lancoreUserId: $userId,
            roles: array_values(array_filter($roles, 'is_string')),
        );
    }
}
