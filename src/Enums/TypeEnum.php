<?php

namespace App\Enums;

enum TypeEnum: string
{
    case Absolute = 'absolute';
    case Relative = 'relative';

    public static function values(): array
    {
        return array_map(static fn(TypeEnum $value): string => $value->value, self::cases());
    }
}
