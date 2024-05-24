<?php

namespace Infrastructure\Symfony\Repository\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Domain\Entity\User;
use Domain\Interface\UserDto\Input\CreateUserInput;

class UserRepositoryImpl extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private EntityManagerInterface $em
    ) {
        parent::__construct($registry, User::class);
    }

    public function getUserByEmail(string $email): ?User
    {
        $user = $this->findOneBy(['email' => $email]);

        return $user;
    }

    public function getUserById(int $userId): ?User
    {
        $user = $this->find($userId);

        return $user;
    }

    public function storeUser(User $newUser): User 
    {
        $connection = $this->em->getConnection();

        $sql = '
            INSERT INTO users (`uuid`, `first_name`, `last_name`, `email`, `password`, `roles`, `status`)
            VALUES (:uuid, :first_name, :last_name, :email, :password, :roles, :status)
        ';

        $connection->executeQuery($sql, [
            'uuid'       => $newUser->uuid,
            'first_name' => $newUser->first_name,
            'last_name'  => $newUser->last_name,
            'email'      => $newUser->email,
            'password'   => $newUser->password,
            'roles'      => json_encode($newUser->getRoles()),
            'status'     => (int) $newUser->status
        ]);

        $inserted = $this->findOneBy(['email' => $newUser->email]);

        return $inserted;
    }

    public function updateUser(User $updatedUser) 
    {
        $this->em->persist($updatedUser);
        $this->em->flush();
    }

    
}