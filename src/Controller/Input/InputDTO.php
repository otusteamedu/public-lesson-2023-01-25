<?php

namespace App\Controller\Input;

use App\Enums\NameEnum;
use App\Enums\TypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class InputDTO
{
    #[Assert\When(
        expression: "this.type === 'absolute'",
        constraints: [
            new Assert\GreaterThan(-1000),
            new Assert\LessThan(1000),
        ]
    )]
    #[Assert\When(
        expression: "this.type === 'relative'",
        constraints: [
            new Assert\GreaterThanOrEqual(0),
            new Assert\LessThanOrEqual(100),
        ]
    )]
    #[Assert\When(
        expression: "this.type === 'name'",
        constraints: [
            new Assert\NotNull(),
            new Assert\Choice(callback: [NameEnum::class, 'names']),
        ]
    )]
    public mixed $value;

    #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
    public string $type;

    public static function fillFromRequest(Request $request): self {
        $result = new self();
        $result->type = $request->request->get('type');
        $result->value = $request->request->get('value');


        return $result;
    }
}
