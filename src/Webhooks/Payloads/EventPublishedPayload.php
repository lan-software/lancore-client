<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class EventPublishedPayload extends WebhookPayload
{
    public function __construct(
        public int $eventId,
        public string $name,
        public ?string $startDate,
        public ?string $endDate,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $id = $request->integer('event.id');

        abort_unless($id > 0, 422, 'Invalid payload.');

        return new static(
            eventId: $id,
            name: (string) $request->input('event.name', ''),
            startDate: $request->input('event.start_date'),
            endDate: $request->input('event.end_date'),
        );
    }
}
