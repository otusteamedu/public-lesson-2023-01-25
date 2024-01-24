<?php

namespace App\Enums;

enum TypeEnum: string
{
    case Absolute = 'absolute';
    case Relative = 'relative';
    case Name = 'name';

    public static function values(): array
    {
        return array_map(static fn(TypeEnum $value): string => $value->value, self::cases());
    }
}
