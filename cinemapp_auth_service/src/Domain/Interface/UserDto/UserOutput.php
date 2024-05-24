<?php

namespace Domain\Interface\UserDto;

use Domain\Entity\User;

class CreateUserOutput
{
    // PROPERTIES
    private function __construct(
        public string $uuid,
        public string $first_name,
        public string $last_name,
        public string $email,
        public array $roles,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt
    ) {}

    public static function create(User $user): self
    {
        return new self(
            $user->uuid,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->getRoles(),
            $user->created_at,
            $user->updated_at
        );
    }

    public function toArray(): array
    {
        return [
            "uuid"          => $this->uuid,
            "first_name"    => $this->first_name,
            "last_name"     => $this->last_name,
            "email"         => $this->email,
            "roles"         => $this->roles,
            "createdAt"     => $this->createdAt->format(\DateTimeInterface::ISO8601_EXPANDED),
            "updatedAt"     => $this->updatedAt->format(\DateTimeInterface::ISO8601_EXPANDED)
        ];
    }
}