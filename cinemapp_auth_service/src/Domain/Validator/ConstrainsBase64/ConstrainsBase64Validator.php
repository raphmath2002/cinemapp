<?php
namespace Domain\Validator\ConstrainsBase64;

use Domain\Validator\ConstrainsBase64\ConstrainsBase64;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ConstrainsBase64Validator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if(!$constraint instanceof ConstrainsBase64) {
            throw new UnexpectedTypeException($constraint, ConstrainsBase64::class);
        }

        if(null === $value || '' === $value) return;

        if(!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if(preg_match('/^data:((?:\w+\/(?:(?!;).)+)?)((?:;[\w\W]*?[^;])*),(.+)$/', $value, $matches)) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', $value)
            ->addViolation();
    }
}