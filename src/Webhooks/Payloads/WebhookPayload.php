<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

abstract readonly class WebhookPayload
{
    abstract public static function fromRequest(Request $request): static;

    protected static function isUlid(mixed $value): bool
    {
        return is_string($value) && preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $value) === 1;
    }

    protected static function ulidOrNull(mixed $value): ?string
    {
        return self::isUlid($value) ? (string) $value : null;
    }
}
