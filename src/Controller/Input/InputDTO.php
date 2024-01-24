<?php

namespace App\Controller\Input;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class InputDTO
{
    #[Assert\Choice(choices: ['correct', 'valid'])]
    public string $choice;

    public static function fillFromRequest(Request $request): self {
        $result = new self();
        $result->choice = $request->request->get('choice');

        return $result;
    }
}
