<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';


    public function label(): string
    {
//        return match($this) {
//            self::PENDING => 'في الانتظار',
//            self::PAID => 'تم الدفع',
//            self::FAILED => 'فشل الدفع',
//            self::REFUNDED => 'تم رد المبلغ ',
//        };

        return __('app.' . $this->value);

    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'danger',
        };
    }
}
