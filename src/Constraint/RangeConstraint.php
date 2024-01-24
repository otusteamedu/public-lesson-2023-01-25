<?php

namespace App\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraints\Compound;
use Symfony\Component\Validator\Constraints as Assert;

#[Attribute]
class RangeConstraint extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\NotNull(),
            new Assert\GreaterThanOrEqual($options['payload']['min']),
            new Assert\LessThan($options['payload']['max']),
        ];
    }
}
