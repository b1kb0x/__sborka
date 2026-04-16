<?php

namespace App\Enums;

enum GrindType: string
{
    case Beans = 'beans';
    case Filter = 'filter';
    case Espresso = 'espresso';
    case Mokapot = 'mokapot';
    case Turkish = 'turkish';

    public function label(): string
    {
        return match ($this) {
            self::Beans => 'В зерне',
            self::Filter => 'Под фильтр',
            self::Espresso => 'Под эспрессо',
            self::Mokapot => 'Под мока-пот',
            self::Turkish => 'Под турку',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}
