<?php

namespace App\Controller\Input;

use App\Enums\ValueEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class InputDTO
{
    #[Assert\Choice(callback: [ValueEnum::class, 'cases'], multiple: true)]
    #[Assert\Type('array')]
    public array $choices;

    public static function fillFromRequest(Request $request): self {
        $result = new self();
        $result->choices = array_map(
            static fn(string $choice): ?ValueEnum => ValueEnum::tryFrom($choice),
            $request->request->all('choices')
        );

        return $result;
    }
}
