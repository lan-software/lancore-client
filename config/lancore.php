<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LanCore Integration
    |--------------------------------------------------------------------------
    |
    | Master toggle for the LanCore integration. When disabled, any call to
    | LanCoreClient throws LanCoreDisabledException immediately.
    |
    */
    'enabled' => env('LANCORE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | LanCore URLs
    |--------------------------------------------------------------------------
    |
    | base_url     — Browser-facing URL used for SSO authorize redirects.
    | internal_url — Server-to-server URL used for API calls (e.g. Docker
    |                service name). Falls back to base_url when not set.
    |
    */
    'base_url' => env('LANCORE_BASE_URL', 'http://lancore.lan'),

    'internal_url' => env('LANCORE_INTERNAL_URL'),

    /*
    |--------------------------------------------------------------------------
    | Integration Credentials
    |--------------------------------------------------------------------------
    */
    'token' => env('LANCORE_TOKEN'),

    'app_slug' => env('LANCORE_APP_SLUG'),

    'callback_url' => env('LANCORE_CALLBACK_URL', env('APP_URL', 'http://localhost').'/auth/lancore/callback'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Tuning
    |--------------------------------------------------------------------------
    */
    'http' => [
        'timeout' => (int) env('LANCORE_TIMEOUT', 5),
        'retries' => (int) env('LANCORE_RETRIES', 2),
        'retry_delay' => (int) env('LANCORE_RETRY_DELAY', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | Single HMAC-SHA256 secret covering all webhook event types. Set to an
    | empty string to bypass verification in local development.
    |
    */
    'webhooks' => [
        'secret' => env('LANCORE_WEBHOOK_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Entrance Sub-Client (opt-in, LanEntrance only)
    |--------------------------------------------------------------------------
    */
    'entrance' => [
        'enabled' => env('LANCORE_ENTRANCE_ENABLED', false),
        'signing_keys_endpoint' => env('LANCORE_SIGNING_KEYS_ENDPOINT', 'api/entrance/signing-keys'),
        'signing_keys_cache_ttl' => (int) env('LANCORE_SIGNING_KEYS_CACHE_TTL', 3600),
        'signing_keys_cache_store' => env('LANCORE_SIGNING_KEYS_CACHE_STORE', 'file'),
        'signing_keys_bootstrap' => env('LANCORE_SIGNING_KEYS_BOOTSTRAP'),
    ],
];
