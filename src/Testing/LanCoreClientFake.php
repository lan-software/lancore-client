<?php

namespace LanSoftware\LanCoreClient\Testing;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use LanSoftware\LanCoreClient\LanCoreClient;

class LanCoreClientFake
{
    /** @var array<string, Response|PromiseInterface|callable> */
    private array $responses = [];

    private function __construct() {}

    /**
     * Create a fake client instance with Http::fake() wired up.
     *
     * Usage in tests:
     *   $fake = LanCoreClientFake::create()
     *       ->withExchangeCode($user)
     *       ->bind();
     */
    public static function create(): self
    {
        return new self;
    }

    /**
     * Register a successful exchangeCode response.
     */
    public function withExchangeCode(LanCoreUser|array $user): self
    {
        $data = $user instanceof LanCoreUser ? $user->toArray() : $user;

        $this->responses['*/api/integration/sso/exchange'] = Http::response([
            'data' => $data,
        ]);

        return $this;
    }

    /**
     * Register a successful currentUser / resolveUser response.
     */
    public function withUser(LanCoreUser|array $user): self
    {
        $data = $user instanceof LanCoreUser ? $user->toArray() : $user;

        $this->responses['*/api/integration/user/*'] = Http::response([
            'data' => $data,
        ]);

        return $this;
    }

    /**
     * Register a failure response for any LanCore endpoint.
     */
    public function withError(int $status = 500, string $error = 'Server error'): self
    {
        $this->responses['*'] = Http::response(['error' => $error], $status);

        return $this;
    }

    /**
     * Register a custom response for a specific URL pattern.
     *
     * @param  Response|PromiseInterface|callable  $response
     */
    public function withResponse(string $urlPattern, mixed $response): self
    {
        $this->responses[$urlPattern] = $response;

        return $this;
    }

    /**
     * Register entrance sub-client responses.
     *
     * @param  array<string, mixed>  $validateResponse
     */
    public function withEntranceValidate(array $validateResponse): self
    {
        $this->responses['*/api/entrance/validate'] = Http::response($validateResponse);

        return $this;
    }

    /**
     * Register signing keys response.
     *
     * @param  array<int, array{kid: string, x: string}>  $keys
     */
    public function withSigningKeys(array $keys): self
    {
        $this->responses['*/api/entrance/signing-keys'] = Http::response(['keys' => $keys]);

        return $this;
    }

    /**
     * Activate Http::fake() with the registered responses and bind
     * the real LanCoreClient (which will hit the faked HTTP layer)
     * into the container.
     */
    public function bind(): LanCoreClient
    {
        Http::fake($this->responses);

        config([
            'lancore.enabled' => true,
            'lancore.base_url' => 'http://lancore.test',
            'lancore.internal_url' => 'http://lancore.test',
            'lancore.token' => 'fake-token',
            'lancore.app_slug' => 'test-app',
            'lancore.callback_url' => 'http://localhost/auth/callback',
            'lancore.http.timeout' => 5,
            'lancore.http.retries' => 0,
            'lancore.http.retry_delay' => 0,
        ]);

        $client = app(LanCoreClient::class);
        app()->instance(LanCoreClient::class, $client);

        return $client;
    }

    /**
     * Assert that a specific LanCore API path was called.
     */
    public static function assertSent(string $urlPattern): void
    {
        Http::assertSent(fn ($request) => str_contains($request->url(), $urlPattern));
    }

    /**
     * Assert that no LanCore API calls were made.
     */
    public static function assertNothingSent(): void
    {
        Http::assertNothingSent();
    }
}
