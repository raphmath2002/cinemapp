<?php

namespace Infrastructure\Symfony\Repository\Auth;

use Doctrine\ORM\EntityManagerInterface;
use Infrastructure\Symfony\Repository\User\UserRepositoryInterface;

class AuthRepositoryImpl implements AuthRepositoryInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected UserRepositoryInterface $userRepository
    ) {
    }

    public function newLoginAttemptFailed(int $userId, ?string $clientIp): void
    {
        $conn = $this->em->getConnection();

        $sql = "INSERT INTO auth_failures (`user_id`,`client_ip`) VALUES (:userId, :clientIp);";

        $conn->executeQuery($sql, ["userId" => $userId, "clientIp" => $clientIp]);
    }

    public function getLastLoginAttemptsFailed(int $userId, ?string $clientIp, int $allowedFailAttemptNb)
    {
        $conn = $this->em->getConnection();

        $sql = "SELECT `created_at` FROM auth_failures WHERE `user_id` = :userId ".
            (!is_null($clientIp) ? "AND `client_ip` = :clientIp" : "")
        ." ORDER BY `created_at` DESC LIMIT $allowedFailAttemptNb;";

        $lastAttempts = $conn->executeQuery($sql, ["userId" => $userId, "clientIp" => $clientIp]);

        return $lastAttempts->fetchAllAssociative();
    }

    public function lockUser(int $userId, int $expireAt)
    {
        $conn = $this->em->getConnection();

        $sql = "INSERT INTO auth_locks (`user_id`,`expire_at`) VALUES (:userId, :expireAt);";

        // un peu dégouté de faire comme ça mais les dates je galère encore `:)
        $timezone = new \DateTimeZone('Europe/Paris'); 
        
        $date = new \DateTime("@$expireAt"); 
        $date->setTimezone($timezone); 
        
        $expireAtDateTime = $date->format('Y-m-d H:i:s'); 

        $conn->executeQuery($sql, ["userId" => $userId, "expireAt" => $expireAtDateTime]);

    }

    public function getUserLock(int $userId)
    {
        $conn = $this->em->getConnection();

        $sql = "SELECT `id` FROM auth_locks WHERE `user_id` = :userId AND `expire_at` > current_timestamp();";

        $lock = $conn->executeQuery($sql, ["userId" => $userId]);

        return $lock->fetchAllAssociative();
    }
}
