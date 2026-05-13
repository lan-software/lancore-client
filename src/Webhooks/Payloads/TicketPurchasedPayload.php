<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class TicketPurchasedPayload extends WebhookPayload
{
    public function __construct(
        public string $ticketId,
        public string $lancoreUserId,
        public string $eventId,
        public ?string $ticketType,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $ticketId = (string) $request->input('ticket.id', '');
        $userId = (string) $request->input('ticket.user_id', '');
        $eventId = (string) $request->input('ticket.event_id', '');

        abort_unless(
            self::isUlid($ticketId) && self::isUlid($userId) && self::isUlid($eventId),
            422,
            'Invalid payload.',
        );

        return new static(
            ticketId: $ticketId,
            lancoreUserId: $userId,
            eventId: $eventId,
            ticketType: $request->input('ticket.ticket_type'),
        );
    }
}
