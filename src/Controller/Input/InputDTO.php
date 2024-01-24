<?php

namespace App\Controller\Input;

use App\Enums\TypeEnum;
use App\Enums\ValueEnum;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    "(this.type === 'absolute' and this.value > -1000 and this.value < 1000) or
     (this.type === 'relative' and this.value >= 0 and this.value <= 100)"
)]
class InputDTO
{
    public int $value;

    #[Assert\Choice(callback: [TypeEnum::class, 'values'])]
    public string $type;

    public static function fillFromRequest(Request $request): self {
        $result = new self();
        $result->type = $request->request->get('type');
        $result->value = $request->request->get('value');

        return $result;
    }
}
