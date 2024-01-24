<?php

namespace App\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class CustomRangeConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomRangeConstraint) {
            throw new UnexpectedTypeException($constraint, CustomRangeConstraint::class);
        }

        if (!is_numeric($value)) {
            throw new UnexpectedValueException($value, 'int');
        }

        if ($value >= $constraint->min && $value <= $constraint->max) {
            return;
        }

        $this->context->buildViolation('Value should be in range [{{ min }}, {{ max }}].')
            ->setParameter('{{ min }}', $constraint->min)
            ->setParameter('{{ max }}', $constraint->max)
            ->addViolation();
    }
}
