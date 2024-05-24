<?php

namespace Domain\Validator\ConstrainsUniqueEntity;

use Symfony\Component\Validator\Constraint;

class ConstrainsUniqueEntity extends Constraint
{
    public $message = "This value is already used";

    public $entityClass;
    public $field;
    public $updatedId = null;

    public function getRequiredOptions(): array
    {
        return ['entityClass', 'field'];
    }

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return get_class($this).'Validator';
    }
}