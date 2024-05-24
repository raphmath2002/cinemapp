<?php

namespace Domain\Validator\ConstrainsUniqueEntity;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstrainsUniqueEntityValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        $entityRepository = $this->em->getRepository($constraint->entityClass);

        if (!is_scalar($constraint->field)) {
            throw new InvalidArgumentException('"field" parameter should be any scalar type');
        }

        $searchResults = $entityRepository->findBy([
            $constraint->field => $value->{$constraint->field}
        ]);

        if (count($searchResults) > 0) {
            if (!is_null($constraint->updatedId) && $searchResults[0]->id === $constraint->updatedId) return;

            $this->context->buildViolation($constraint->message)
                ->atPath($constraint->field)
                ->addViolation();
        }
    }
}
