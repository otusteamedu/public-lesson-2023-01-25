<?php

namespace App\Enums;

enum NameEnum: int
{
    case Large = 20;
    case Medium = 15;
    case Small = 10;

    public static function values(): array
    {
        return array_map(static fn(NameEnum $value): int => $value->value, self::cases());
    }

    public static function names(): array
    {
        return array_map(static fn(NameEnum $value): string => $value->name, self::cases());
    }

    public static function getValueByName(string $name): ?int
    {
        return array_combine(self::names(), self::cases())[$name] ?? null;
    }
}
