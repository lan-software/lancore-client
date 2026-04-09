<?php

namespace LanSoftware\LanCoreClient\DTOs;

use Carbon\CarbonImmutable;
use LanSoftware\LanCoreClient\Exceptions\InvalidLanCoreUserException;

readonly class LanCoreUser
{
    /**
     * @param  list<string>  $roles
     */
    public function __construct(
        public int $id,
        public string $username,
        public ?string $email = null,
        public ?string $locale = null,
        public ?string $avatar = null,
        public array $roles = [],
        public ?CarbonImmutable $createdAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidLanCoreUserException
     */
    public static function fromArray(array $data): self
    {
        if (empty($data['id']) || empty($data['username'])) {
            throw new InvalidLanCoreUserException;
        }

        return new self(
            id: (int) $data['id'],
            username: (string) $data['username'],
            email: isset($data['email']) && is_string($data['email']) ? $data['email'] : null,
            locale: $data['locale'] ?? null,
            avatar: $data['avatar_url'] ?? $data['avatar'] ?? null,
            roles: array_values(array_filter($data['roles'] ?? [], 'is_string')),
            createdAt: isset($data['created_at']) ? CarbonImmutable::parse($data['created_at']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'locale' => $this->locale,
            'avatar' => $this->avatar,
            'roles' => $this->roles,
            'created_at' => $this->createdAt?->toIso8601String(),
        ];
    }
}
