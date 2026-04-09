<?php

namespace LanSoftware\LanCoreClient;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use LanSoftware\LanCoreClient\DTOs\LanCoreUser;
use LanSoftware\LanCoreClient\Entrance\EntranceClient;
use LanSoftware\LanCoreClient\Exceptions\InvalidLanCoreUserException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreDisabledException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreRequestException;
use LanSoftware\LanCoreClient\Exceptions\LanCoreUnavailableException;

class LanCoreClient
{
    private ?EntranceClient $entranceClient = null;

    /**
     * Build the URL the browser should be redirected to for SSO authorization.
     *
     * @throws LanCoreDisabledException
     */
    public function ssoAuthorizeUrl(): string
    {
        $this->ensureEnabled();

        return rtrim((string) config('lancore.base_url'), '/').'/sso/authorize?'.http_build_query([
            'app' => config('lancore.app_slug'),
            'redirect_uri' => config('lancore.callback_url'),
        ]);
    }

    /**
     * Exchange a single-use SSO authorization code for a LanCoreUser.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreUnavailableException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function exchangeCode(string $code): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->request(
            fn (PendingRequest $http) => $http->post('/api/integration/sso/exchange', ['code' => $code])
        );

        return $this->parseUser($response->json('data', []));
    }

    /**
     * Fetch the currently authenticated integration user.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreUnavailableException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function currentUser(): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->request(
            fn (PendingRequest $http) => $http->get('/api/integration/user/me')
        );

        return $this->parseUser($response->json('data', []));
    }

    /**
     * Resolve a LanCore user by their ID.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreUnavailableException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function resolveUserById(int $id): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->request(
            fn (PendingRequest $http) => $http->post('/api/integration/user/resolve', ['user_id' => $id])
        );

        return $this->parseUser($response->json('data', []));
    }

    /**
     * Resolve a LanCore user by their email address.
     *
     * @throws LanCoreDisabledException
     * @throws LanCoreUnavailableException
     * @throws LanCoreRequestException
     * @throws InvalidLanCoreUserException
     */
    public function resolveUserByEmail(string $email): LanCoreUser
    {
        $this->ensureEnabled();

        $response = $this->request(
            fn (PendingRequest $http) => $http->post('/api/integration/user/resolve', ['email' => $email])
        );

        return $this->parseUser($response->json('data', []));
    }

    /**
     * Access the Entrance sub-client (opt-in, LanEntrance only).
     *
     * @throws LanCoreDisabledException
     */
    public function entrance(): EntranceClient
    {
        $this->ensureEnabled();

        if (! config('lancore.entrance.enabled')) {
            throw new LanCoreDisabledException('LanCore Entrance sub-client is disabled.');
        }

        return $this->entranceClient ??= new EntranceClient($this);
    }

    /**
     * Execute an HTTP request against the LanCore Integration API with
     * retry/timeout semantics and unified error handling.
     *
     * @param  callable(PendingRequest): Response  $callback
     *
     * @throws LanCoreUnavailableException
     * @throws LanCoreRequestException
     *
     * @internal Used by EntranceClient — not part of the public API.
     */
    public function request(callable $callback): Response
    {
        try {
            $response = $callback($this->http());
        } catch (ConnectionException $e) {
            throw new LanCoreUnavailableException('LanCore is unreachable: '.$e->getMessage(), 0, $e);
        } catch (RequestException $e) {
            $status = $e->response?->status() ?? 0;

            if ($status >= 500) {
                throw new LanCoreUnavailableException(
                    'LanCore returned a server error: '.$status,
                    $status,
                    $e,
                );
            }

            throw new LanCoreRequestException(
                $e->response?->json('error') ?? $e->getMessage(),
                $status,
                $e,
            );
        }

        if ($response->serverError()) {
            throw new LanCoreUnavailableException(
                'LanCore returned a server error: '.$response->status(),
                $response->status(),
            );
        }

        if ($response->clientError()) {
            throw new LanCoreRequestException(
                $response->json('error') ?? 'LanCore request failed.',
                $response->status(),
            );
        }

        return $response;
    }

    /**
     * @throws LanCoreDisabledException
     */
    private function ensureEnabled(): void
    {
        if (! config('lancore.enabled')) {
            throw new LanCoreDisabledException;
        }
    }

    private function http(): PendingRequest
    {
        $baseUrl = config('lancore.internal_url') ?? config('lancore.base_url');

        return Http::baseUrl(rtrim((string) $baseUrl, '/'))
            ->timeout((int) config('lancore.http.timeout', 5))
            ->retry((int) config('lancore.http.retries', 2), (int) config('lancore.http.retry_delay', 100))
            ->withToken((string) config('lancore.token'))
            ->acceptJson();
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidLanCoreUserException
     */
    private function parseUser(array $data): LanCoreUser
    {
        return LanCoreUser::fromArray($data);
    }
}
