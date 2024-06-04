<?php

namespace Infrastructure\Symfony\Repository\User;

use Domain\Entity\User;
use Domain\Interface\UserDto\Input\CreateUserInput;

interface UserRepositoryInterface
{
    public function storeUser(User $newUser): User;
    public function getUserByEmail(string $email): ?User;
    public function getUserById(string $userUuid): ?User;
    public function updateUser(User $updatedUser);
}