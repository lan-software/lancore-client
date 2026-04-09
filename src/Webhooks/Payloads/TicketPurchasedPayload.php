<?php

namespace LanSoftware\LanCoreClient\Webhooks\Payloads;

use Illuminate\Http\Request;

readonly class TicketPurchasedPayload extends WebhookPayload
{
    public function __construct(
        public int $ticketId,
        public int $lancoreUserId,
        public int $eventId,
        public ?string $ticketType,
    ) {}

    public static function fromRequest(Request $request): static
    {
        $ticketId = $request->integer('ticket.id');
        $userId = $request->integer('ticket.user_id');

        abort_unless($ticketId > 0 && $userId > 0, 422, 'Invalid payload.');

        return new static(
            ticketId: $ticketId,
            lancoreUserId: $userId,
            eventId: $request->integer('ticket.event_id'),
            ticketType: $request->input('ticket.ticket_type'),
        );
    }
}
