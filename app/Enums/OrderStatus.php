<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending   = 'pending';
    case Processing = 'processing';
    case delivering = 'delivering';
    case Completed = 'completed';
    case Canceled  = 'canceled';
    case refunded  = 'refunded';

    // اسم الحالة للعرض
    public function label(): string
    {
        // return match ($this) {
        //     self::Pending    => 'قيد الانتظار',
        //     self::Processing => 'قيد التنفيذ',
        //     self::delivering => 'قيد الشحن',
        //     self::Completed  => 'مكتمل',
        //     self::Canceled   => 'ملغي',
        //     self::refunded   => 'تم ارجاعة',
        // };
        return __('orders.status.' . $this->value);
    }

    // لون الحالة (مفيد في Blade)
    public function color(): string
    {
        return match ($this) {
            self::Pending    => 'warning',
            self::Processing => 'info',
            self::delivering => 'primary',
            self::Completed  => 'success',
            self::Canceled   => 'danger',
            self::refunded   => 'danger',

        };
    }
}
