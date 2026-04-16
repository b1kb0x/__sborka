<?php

namespace App\Enums;

enum OrderStatus: string
{
    case New = 'new';
    case Paid = 'paid';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Новый',
            self::Paid => 'Оплачен',
            self::Cancelled => 'Отменён',
            self::Completed => 'Завершён',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::New => in_array($next, [self::Paid, self::Cancelled], true),
            self::Paid => in_array($next, [self::Completed, self::Cancelled], true),
            self::Completed => false,
            self::Cancelled => false,
        };
    }
}
