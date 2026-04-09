<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

abstract readonly class WebhookPayload
{
    abstract public static function fromRequest(Request $request): static;
}
