<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING        = 'pending';
    case PROCESSING     = 'processing';
    case SHIPPED        = 'shipped';
    case DELIVERED      = 'delivered';
    case COMPLETED      = 'completed';
    case CANCELED       = 'canceled';
    case REFUNDED       = 'refunded';

    // اسم الحالة للعرض
    public function label(): string
    {
        // return match ($this) {
        //     self::Pending    => 'قيد الانتظار',
        //     self::Processing => 'جارى التنفيذ',
        //     self::delivering => 'قيد الشحن',
        //     self::Completed  => 'مكتمل',
        //     self::Canceled   => 'ملغي',
        //     self::refunded   => 'تم ارجاعة',
        // };
        return __('app.' . $this->value);
    }

    // لون الحالة (مفيد في Blade)
    public function color(): string
    {
        return match ($this) {
            self::PENDING       => 'warning',
            self::PROCESSING    => 'info',
            self::SHIPPED       => 'primary',
            self::DELIVERED     => 'success',
            self::COMPLETED     => 'success',
            self::CANCELED      => 'danger',
            self::REFUNDED      => 'danger',

        };
    }
}
