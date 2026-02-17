<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Active     = 'active';
    case Archived   = 'archived';
    case Draft      = 'draft';
    

    // اسم الحالة للعرض
    public function label(): string
    {
        // return match ($this) {
        //     self::Active    => 'قيد الانتظار',
        //     self::Archived => 'قيد التنفيذ',
        //     self::Draft => 'قيد الشحن',
       
        return __('app.' . $this->value);
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

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => $case->label()
            ])
            ->toArray();
    }
}
