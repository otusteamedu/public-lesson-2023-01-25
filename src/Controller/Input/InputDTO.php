<?php

namespace App\Controller\Input;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class InputDTO
{
    #[Assert\Choice(choices: ['correct', 'valid'], multiple: true)]
    #[Assert\Type('array')]
    public array $choices;

    public static function fillFromRequest(Request $request): self {
        $result = new self();
        $result->choices = $request->request->all('choices');

        return $result;
    }
}
