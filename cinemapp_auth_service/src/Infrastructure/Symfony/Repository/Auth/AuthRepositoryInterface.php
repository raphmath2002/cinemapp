<?php

namespace Infrastructure\Symfony\Repository\Auth;

interface AuthRepositoryInterface 
{
    public function newLoginAttemptFailed(int $userId, ?string $clientIp): void;
    public function getLastLoginAttemptsFailed(int $userId, ?string $clientIp, int $allowedFailAttemptNb);
    public function lockUser(int $userId, int $expireAt);
    public function getUserLock(int $userId);
}