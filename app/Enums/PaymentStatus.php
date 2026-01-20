<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Canceled  = 'failed';
    case refunded  = 'refunded';

    
    public function label(): string
    {
        return match ($this) {
            self::Pending    => 'قيد الانتظار',
            self::Completed  => 'مكتمل',
            self::Canceled   => 'ملغي',
            self::refunded   => 'تم ارجاعة',
        };
    }

   
    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'warning',
            self::Completed  => 'success',
            self::Canceled   => 'danger',
            self::refunded   => 'danger',

        };
    }
}
