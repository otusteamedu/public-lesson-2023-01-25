<?php

namespace App\Constraint;

use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class CustomRangeConstraint extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public readonly int $min,
        public readonly int $max,
        array $groups = null,
        $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }
}
