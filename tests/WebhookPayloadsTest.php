<?php

use Illuminate\Http\Request;
use LanSoftware\LanCoreClient\Webhooks\Payloads\AnnouncementPublishedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\EventPublishedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\IntegrationAccessedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\NewsArticlePublishedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\TicketPurchasedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\UserProfileUpdatedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\UserRegisteredPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\UserRolesUpdatedPayload;
use LanSoftware\LanCoreClient\Webhooks\Payloads\WebhookPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;

function jsonRequest(array $body): Request
{
    return Request::create('/webhook', 'POST', $body);
}

// ─── UserRegistered ────────────────────────────────────────────────────────────

it('parses a valid user.registered payload', function () {
    $request = jsonRequest([
        'user' => [
            'id' => 42,
            'username' => 'alice',
            'email' => 'alice@example.com',
        ],
    ]);

    $payload = UserRegisteredPayload::fromRequest($request);

    expect($payload)
        ->toBeInstanceOf(UserRegisteredPayload::class)
        ->lancoreUserId->toBe(42)
        ->username->toBe('alice')
        ->email->toBe('alice@example.com');
});

it('accepts a user.registered payload without an email', function () {
    $payload = UserRegisteredPayload::fromRequest(jsonRequest([
        'user' => ['id' => 1, 'username' => 'bob'],
    ]));

    expect($payload->email)->toBeNull();
});

it('coerces a missing username to an empty string in user.registered', function () {
    $payload = UserRegisteredPayload::fromRequest(jsonRequest([
        'user' => ['id' => 1],
    ]));

    expect($payload->username)->toBe('');
});

// ─── UserRolesUpdated ──────────────────────────────────────────────────────────

it('parses a valid user.roles_updated payload', function () {
    $payload = UserRolesUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => 7, 'roles' => ['admin', 'moderator']],
    ]));

    expect($payload)
        ->lancoreUserId->toBe(7)
        ->roles->toBe(['admin', 'moderator']);
});

it('filters non-string entries from user.roles_updated payload', function () {
    $payload = UserRolesUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => 7, 'roles' => ['admin', 123, null, 'user', false]],
    ]));

    expect($payload->roles)->toBe(['admin', 'user']);
});

it('aborts when user.roles_updated has no roles array', function () {
    UserRolesUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => 1, 'roles' => 'admin'],
    ]));
})->throws(HttpException::class);

// ─── AnnouncementPublished ─────────────────────────────────────────────────────

it('parses a valid announcement.published payload', function () {
    $payload = AnnouncementPublishedPayload::fromRequest(jsonRequest([
        'announcement' => [
            'id' => 99,
            'title' => 'Server maintenance tonight',
            'priority' => 'high',
            'event_id' => 5,
        ],
    ]));

    expect($payload)
        ->announcementId->toBe(99)
        ->title->toBe('Server maintenance tonight')
        ->priority->toBe('high')
        ->eventId->toBe(5);
});

it('defaults announcement.priority to "normal" when missing', function () {
    $payload = AnnouncementPublishedPayload::fromRequest(jsonRequest([
        'announcement' => ['id' => 1, 'title' => 'Hi'],
    ]));

    expect($payload->priority)->toBe('normal');
});

it('treats a missing announcement.event_id as null, not zero', function () {
    $payload = AnnouncementPublishedPayload::fromRequest(jsonRequest([
        'announcement' => ['id' => 1, 'title' => 'Hi'],
    ]));

    expect($payload->eventId)->toBeNull();
});

// ─── EventPublished ────────────────────────────────────────────────────────────

it('parses a valid event.published payload', function () {
    $payload = EventPublishedPayload::fromRequest(jsonRequest([
        'event' => [
            'id' => 12,
            'name' => 'LAN Spring 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-03',
        ],
    ]));

    expect($payload)
        ->eventId->toBe(12)
        ->name->toBe('LAN Spring 2026')
        ->startDate->toBe('2026-05-01')
        ->endDate->toBe('2026-05-03');
});

it('keeps event date fields nullable when missing', function () {
    $payload = EventPublishedPayload::fromRequest(jsonRequest([
        'event' => ['id' => 12, 'name' => 'Untitled'],
    ]));

    expect($payload)
        ->startDate->toBeNull()
        ->endDate->toBeNull();
});

// ─── IntegrationAccessed ───────────────────────────────────────────────────────

it('parses a valid integration.accessed payload', function () {
    $payload = IntegrationAccessedPayload::fromRequest(jsonRequest([
        'integration' => [
            'id' => 3,
            'user_id' => 42,
            'app_slug' => 'lanbrackets',
        ],
    ]));

    expect($payload)
        ->integrationId->toBe(3)
        ->lancoreUserId->toBe(42)
        ->appSlug->toBe('lanbrackets');
});

it('aborts when integration.accessed is missing user_id', function () {
    IntegrationAccessedPayload::fromRequest(jsonRequest([
        'integration' => ['id' => 3, 'app_slug' => 'lanbrackets'],
    ]));
})->throws(HttpException::class);

// ─── TicketPurchased ───────────────────────────────────────────────────────────

it('parses a valid ticket.purchased payload', function () {
    $payload = TicketPurchasedPayload::fromRequest(jsonRequest([
        'ticket' => [
            'id' => 100,
            'user_id' => 42,
            'event_id' => 5,
            'ticket_type' => 'standard',
        ],
    ]));

    expect($payload)
        ->ticketId->toBe(100)
        ->lancoreUserId->toBe(42)
        ->eventId->toBe(5)
        ->ticketType->toBe('standard');
});

it('keeps ticket.ticket_type nullable when missing', function () {
    $payload = TicketPurchasedPayload::fromRequest(jsonRequest([
        'ticket' => ['id' => 1, 'user_id' => 2],
    ]));

    expect($payload->ticketType)->toBeNull();
});

// ─── NewsArticlePublished ──────────────────────────────────────────────────────

it('parses a valid news_article.published payload', function () {
    $payload = NewsArticlePublishedPayload::fromRequest(jsonRequest([
        'article' => [
            'id' => 7,
            'title' => 'New event format',
            'slug' => 'new-event-format',
        ],
    ]));

    expect($payload)
        ->articleId->toBe(7)
        ->title->toBe('New event format')
        ->slug->toBe('new-event-format');
});

// ─── UserProfileUpdated ────────────────────────────────────────────────────────

it('parses a valid user.profile_updated payload', function () {
    $payload = UserProfileUpdatedPayload::fromRequest(jsonRequest([
        'user' => [
            'id' => 42,
            'changes' => ['email' => 'new@example.com', 'locale' => 'de'],
        ],
    ]));

    expect($payload)
        ->lancoreUserId->toBe(42)
        ->changes->toBe(['email' => 'new@example.com', 'locale' => 'de']);
});

it('treats a missing user.changes object as an empty array', function () {
    $payload = UserProfileUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => 42],
    ]));

    expect($payload->changes)->toBe([]);
});

// ─── Cross-cutting validation: every payload must reject a missing/zero ID ─────

it('aborts with HTTP 422 when the primary identifier is missing or non-positive', function (string $payloadClass, array $body) {
    /** @var class-string<WebhookPayload> $payloadClass */
    $payloadClass::fromRequest(jsonRequest($body));
})
    ->with([
        'UserRegistered without user.id' => [UserRegisteredPayload::class, ['user' => ['username' => 'x']]],
        'UserRegistered with user.id=0' => [UserRegisteredPayload::class, ['user' => ['id' => 0]]],
        'UserRegistered with user.id=-1' => [UserRegisteredPayload::class, ['user' => ['id' => -1]]],
        'UserRolesUpdated without user.id' => [UserRolesUpdatedPayload::class, ['user' => ['roles' => []]]],
        'UserProfileUpdated without user.id' => [UserProfileUpdatedPayload::class, ['user' => []]],
        'AnnouncementPublished without id' => [AnnouncementPublishedPayload::class, ['announcement' => ['title' => 't']]],
        'EventPublished without event.id' => [EventPublishedPayload::class, ['event' => ['name' => 'x']]],
        'IntegrationAccessed without id' => [IntegrationAccessedPayload::class, ['integration' => ['user_id' => 1]]],
        'TicketPurchased without ticket.id' => [TicketPurchasedPayload::class, ['ticket' => ['user_id' => 1]]],
        'NewsArticlePublished without id' => [NewsArticlePublishedPayload::class, ['article' => ['title' => 'x']]],
    ])
    ->throws(HttpException::class);
