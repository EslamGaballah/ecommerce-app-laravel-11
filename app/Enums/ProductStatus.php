<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Active   = 'pending';
    case Archived = 'processing';
    case Draft = 'delivering';
    

    // اسم الحالة للعرض
    public function label(): string
    {
        // return match ($this) {
        //     self::Active    => 'قيد الانتظار',
        //     self::Archived => 'قيد التنفيذ',
        //     self::Draft => 'قيد الشحن',
       
        return __('product.status.' . $this->value);
    }

    // لون الحالة (مفيد في Blade)
    public function color(): string
    {
        return match ($this) {
            self::Active    => 'success',
            self::Archived => 'info',
            self::Draft => 'primary',
            // self::Completed  => 'success',
            // self::Canceled   => 'danger',
            // self::refunded   => 'danger',

        };
    }
}
