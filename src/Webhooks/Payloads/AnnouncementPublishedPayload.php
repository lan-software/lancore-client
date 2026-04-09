<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class AnnouncementPublishedPayload extends WebhookPayload
{
    public function __construct(
        public int $announcementId,
        public string $title,
        public string $priority,
        public ?int $eventId,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $id = $request->integer('announcement.id');

        abort_unless($id > 0, 422, 'Invalid payload.');

        return new static(
            announcementId: $id,
            title: (string) $request->input('announcement.title', ''),
            priority: (string) $request->input('announcement.priority', 'normal'),
            eventId: $request->input('announcement.event_id') ? (int) $request->input('announcement.event_id') : null,
        );
    }
}
