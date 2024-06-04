<?php

namespace Domain\Interface\UserDto\Input;

use Domain\Entity\User;
use Domain\Validator\ConstrainsUniqueEntity\ConstrainsUniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\PasswordStrength;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UpdateUserInput
{

    // PROPERTIES
    public string   $uuid        = '';
    public ?string  $first_name  = null;
    public ?string  $last_name   = null;
    public ?string  $email       = null;
    public ?array   $roles       = null;
    public ?bool    $status      = null;

    // VALIDATOR
    public static function loadValidatorMetaData(ClassMetadata $metadata): void
    {

        $metadata->addPropertyConstraints('first_name', [
            new Assert\Type('string'),
            new Assert\Length(min: 3, max: 40)
        ]);

        $metadata->addPropertyConstraints('last_name', [
            new Assert\Type('string'),
            new Assert\Length(min: 3, max: 40)
        ]);

        $metadata->addPropertyConstraints('email', [
            new Assert\Type('string'),
            new Assert\Email()
        ]);

        $metadata->addPropertyConstraints('roles', [
            new Assert\Type('array')
        ]);

        $metadata->addPropertyConstraints('status', [
            new Assert\Type('bool')
        ]);
    }

    public function toArray(): array
    {
        return [
            "first_name"    => $this->first_name,
            "last_name"     => $this->last_name,
            "email"         => $this->email,
            "roles"         => $this->roles,
            "status"        => $this->status,
        ];
    }
}
