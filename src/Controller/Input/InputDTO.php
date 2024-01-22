<?php

namespace App\Controller\Input;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class InputDTO
{
    #[Assert\NotBlank]
    public string $notEmptyString;

    #[Assert\NotNull]
    public ?string $notNullString;

    #[Assert\Type('integer')]
    public $int;

    public static function fillFromRequest(Request $request): self {
        $result = new self();
        $result->notEmptyString = $request->request->get('notEmptyString');
        $result->notNullString = $request->request->get('notNullString');
        $result->int = $request->request->get('int');

        return $result;
    }
}
