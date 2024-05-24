<?php

namespace Domain\Interface\UserDto\Input;

use Domain\Entity\User;
use Domain\Validator\ConstrainsUniqueEntity\ConstrainsUniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class CreateUserInput
{

    // PROPERTIES
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $password;
    public ?array $roles = ["ROLE_USER"];
    public ?bool $status = true;

    // VALIDATOR
    public static function loadValidatorMetaData(ClassMetadata $metadata): void
    {

        $metadata->addConstraint(new ConstrainsUniqueEntity([
            'entityClass' => User::class,
            'field' => 'email'
        ]));

        $metadata->addPropertyConstraints('first_name', [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('string'),
            new Assert\Length(min: 3, max: 40)
        ]);

        $metadata->addPropertyConstraints('last_name', [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('string'),
            new Assert\Length(min: 3, max: 40)
        ]);

        $metadata->addPropertyConstraints('email', [
            new Assert\NotBlank(),
            new Assert\NotNull(),
            new Assert\Type('string'),
            new Assert\Email()
        ]);

        $metadata->addPropertyConstraints('password', [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\PasswordStrength([
                'minScore' => PasswordStrength::STRENGTH_WEAK
            ])
        ]);

        $metadata->addPropertyConstraints('roles', [
            new Assert\Type('array')
        ]);

        $metadata->addPropertyConstraints('status', [
            new Assert\Type('bool')
        ]);
    }

    public function transform(): User {
        $user = new User();

        $user->first_name   = $this->first_name;
        $user->last_name    = $this->last_name;
        $user->email        = $this->email;
        $user->password     = $this->password;
        $user->status       = $this->status;

        $user->setRoles($this->roles);

        return $user;
    }
}
