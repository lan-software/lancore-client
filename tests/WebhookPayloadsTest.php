<?php

use Illuminate\Http\Request;
use Illuminate\Support\Str;
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

function ulid(): string
{
    return (string) Str::ulid();
}

// ─── UserRegistered ────────────────────────────────────────────────────────────

it('parses a valid user.registered payload', function () {
    $userId = ulid();

    $request = jsonRequest([
        'user' => [
            'id' => $userId,
            'username' => 'alice',
            'email' => 'alice@example.com',
        ],
    ]);

    $payload = UserRegisteredPayload::fromRequest($request);

    expect($payload)
        ->toBeInstanceOf(UserRegisteredPayload::class)
        ->lancoreUserId->toBe($userId)
        ->username->toBe('alice')
        ->email->toBe('alice@example.com');
});

it('accepts a user.registered payload without an email', function () {
    $payload = UserRegisteredPayload::fromRequest(jsonRequest([
        'user' => ['id' => ulid(), 'username' => 'bob'],
    ]));

    expect($payload->email)->toBeNull();
});

it('coerces a missing username to an empty string in user.registered', function () {
    $payload = UserRegisteredPayload::fromRequest(jsonRequest([
        'user' => ['id' => ulid()],
    ]));

    expect($payload->username)->toBe('');
});

// ─── UserRolesUpdated ──────────────────────────────────────────────────────────

it('parses a valid user.roles_updated payload', function () {
    $userId = ulid();

    $payload = UserRolesUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => $userId, 'roles' => ['admin', 'moderator']],
    ]));

    expect($payload)
        ->lancoreUserId->toBe($userId)
        ->roles->toBe(['admin', 'moderator']);
});

it('filters non-string entries from user.roles_updated payload', function () {
    $payload = UserRolesUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => ulid(), 'roles' => ['admin', 123, null, 'user', false]],
    ]));

    expect($payload->roles)->toBe(['admin', 'user']);
});

it('aborts when user.roles_updated has no roles array', function () {
    UserRolesUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => ulid(), 'roles' => 'admin'],
    ]));
})->throws(HttpException::class);

// ─── AnnouncementPublished ─────────────────────────────────────────────────────

it('parses a valid announcement.published payload', function () {
    $announcementId = ulid();
    $eventId = ulid();

    $payload = AnnouncementPublishedPayload::fromRequest(jsonRequest([
        'announcement' => [
            'id' => $announcementId,
            'title' => 'Server maintenance tonight',
            'priority' => 'high',
            'event_id' => $eventId,
        ],
    ]));

    expect($payload)
        ->announcementId->toBe($announcementId)
        ->title->toBe('Server maintenance tonight')
        ->priority->toBe('high')
        ->eventId->toBe($eventId);
});

it('defaults announcement.priority to "normal" when missing', function () {
    $payload = AnnouncementPublishedPayload::fromRequest(jsonRequest([
        'announcement' => ['id' => ulid(), 'title' => 'Hi'],
    ]));

    expect($payload->priority)->toBe('normal');
});

it('treats a missing announcement.event_id as null', function () {
    $payload = AnnouncementPublishedPayload::fromRequest(jsonRequest([
        'announcement' => ['id' => ulid(), 'title' => 'Hi'],
    ]));

    expect($payload->eventId)->toBeNull();
});

// ─── EventPublished ────────────────────────────────────────────────────────────

it('parses a valid event.published payload', function () {
    $eventId = ulid();

    $payload = EventPublishedPayload::fromRequest(jsonRequest([
        'event' => [
            'id' => $eventId,
            'name' => 'LAN Spring 2026',
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-03',
        ],
    ]));

    expect($payload)
        ->eventId->toBe($eventId)
        ->name->toBe('LAN Spring 2026')
        ->startDate->toBe('2026-05-01')
        ->endDate->toBe('2026-05-03');
});

it('keeps event date fields nullable when missing', function () {
    $payload = EventPublishedPayload::fromRequest(jsonRequest([
        'event' => ['id' => ulid(), 'name' => 'Untitled'],
    ]));

    expect($payload)
        ->startDate->toBeNull()
        ->endDate->toBeNull();
});

// ─── IntegrationAccessed ───────────────────────────────────────────────────────

it('parses a valid integration.accessed payload', function () {
    $integrationId = ulid();
    $userId = ulid();

    $payload = IntegrationAccessedPayload::fromRequest(jsonRequest([
        'integration' => [
            'id' => $integrationId,
            'user_id' => $userId,
            'app_slug' => 'lanbrackets',
        ],
    ]));

    expect($payload)
        ->integrationId->toBe($integrationId)
        ->lancoreUserId->toBe($userId)
        ->appSlug->toBe('lanbrackets');
});

it('aborts when integration.accessed is missing user_id', function () {
    IntegrationAccessedPayload::fromRequest(jsonRequest([
        'integration' => ['id' => ulid(), 'app_slug' => 'lanbrackets'],
    ]));
})->throws(HttpException::class);

// ─── TicketPurchased ───────────────────────────────────────────────────────────

it('parses a valid ticket.purchased payload', function () {
    $ticketId = ulid();
    $userId = ulid();
    $eventId = ulid();

    $payload = TicketPurchasedPayload::fromRequest(jsonRequest([
        'ticket' => [
            'id' => $ticketId,
            'user_id' => $userId,
            'event_id' => $eventId,
            'ticket_type' => 'standard',
        ],
    ]));

    expect($payload)
        ->ticketId->toBe($ticketId)
        ->lancoreUserId->toBe($userId)
        ->eventId->toBe($eventId)
        ->ticketType->toBe('standard');
});

it('keeps ticket.ticket_type nullable when missing', function () {
    $payload = TicketPurchasedPayload::fromRequest(jsonRequest([
        'ticket' => ['id' => ulid(), 'user_id' => ulid(), 'event_id' => ulid()],
    ]));

    expect($payload->ticketType)->toBeNull();
});

// ─── NewsArticlePublished ──────────────────────────────────────────────────────

it('parses a valid news_article.published payload', function () {
    $articleId = ulid();

    $payload = NewsArticlePublishedPayload::fromRequest(jsonRequest([
        'article' => [
            'id' => $articleId,
            'title' => 'New event format',
            'slug' => 'new-event-format',
        ],
    ]));

    expect($payload)
        ->articleId->toBe($articleId)
        ->title->toBe('New event format')
        ->slug->toBe('new-event-format');
});

// ─── UserProfileUpdated ────────────────────────────────────────────────────────

it('parses a valid user.profile_updated payload', function () {
    $userId = ulid();

    $payload = UserProfileUpdatedPayload::fromRequest(jsonRequest([
        'user' => [
            'id' => $userId,
            'changes' => ['email' => 'new@example.com', 'locale' => 'de'],
        ],
    ]));

    expect($payload)
        ->lancoreUserId->toBe($userId)
        ->changes->toBe(['email' => 'new@example.com', 'locale' => 'de']);
});

it('treats a missing user.changes object as an empty array', function () {
    $payload = UserProfileUpdatedPayload::fromRequest(jsonRequest([
        'user' => ['id' => ulid()],
    ]));

    expect($payload->changes)->toBe([]);
});

// ─── Cross-cutting validation: every payload must reject a missing/non-ULID identifier ─────

it('aborts with HTTP 422 when the primary identifier is missing or not a valid ULID', function (string $payloadClass, array $body) {
    /** @var class-string<WebhookPayload> $payloadClass */
    $payloadClass::fromRequest(jsonRequest($body));
})
    ->with([
        'UserRegistered without user.id' => [UserRegisteredPayload::class, ['user' => ['username' => 'x']]],
        'UserRegistered with int user.id' => [UserRegisteredPayload::class, ['user' => ['id' => 42]]],
        'UserRegistered with malformed user.id' => [UserRegisteredPayload::class, ['user' => ['id' => 'not-a-ulid']]],
        'UserRolesUpdated without user.id' => [UserRolesUpdatedPayload::class, ['user' => ['roles' => []]]],
        'UserProfileUpdated without user.id' => [UserProfileUpdatedPayload::class, ['user' => []]],
        'AnnouncementPublished without id' => [AnnouncementPublishedPayload::class, ['announcement' => ['title' => 't']]],
        'EventPublished without event.id' => [EventPublishedPayload::class, ['event' => ['name' => 'x']]],
        'IntegrationAccessed without id' => [IntegrationAccessedPayload::class, ['integration' => ['user_id' => 1]]],
        'TicketPurchased without ticket.id' => [TicketPurchasedPayload::class, ['ticket' => ['user_id' => 1]]],
        'NewsArticlePublished without id' => [NewsArticlePublishedPayload::class, ['article' => ['title' => 'x']]],
    ])
    ->throws(HttpException::class);
