<?php

namespace App\Enums;

enum FulfillmentStatus: string
{
    case Accepted = 'accepted';
    case Roasting = 'roasting';
    case HandedToCarrier = 'handed_to_carrier';
    case Delivered = 'delivered';

    public function label(): string
    {
        return match ($this) {
            self::Accepted => 'Принято',
            self::Roasting => 'В обжарке',
            self::HandedToCarrier => 'Передано в транспортную компанию',
            self::Delivered => 'Доставлено',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases()
        );
    }
}
