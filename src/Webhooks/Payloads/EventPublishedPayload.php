<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class EventPublishedPayload extends WebhookPayload
{
    public function __construct(
        public string $eventId,
        public string $name,
        public ?string $startDate,
        public ?string $endDate,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $id = (string) $request->input('event.id', '');

        abort_unless(self::isUlid($id), 422, 'Invalid payload.');

        return new static(
            eventId: $id,
            name: (string) $request->input('event.name', ''),
            startDate: $request->input('event.start_date'),
            endDate: $request->input('event.end_date'),
        );
    }
}
