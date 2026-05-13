<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class AnnouncementPublishedPayload extends WebhookPayload
{
    public function __construct(
        public string $announcementId,
        public string $title,
        public string $priority,
        public ?string $eventId,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $id = (string) $request->input('announcement.id', '');

        abort_unless(self::isUlid($id), 422, 'Invalid payload.');

        return new static(
            announcementId: $id,
            title: (string) $request->input('announcement.title', ''),
            priority: (string) $request->input('announcement.priority', 'normal'),
            eventId: self::ulidOrNull($request->input('announcement.event_id')),
        );
    }
}
