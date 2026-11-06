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

    /**
     * Orders only move forward. Canceling is possible until delivery, refunding only after it,
     * and a canceled or refunded order is closed.
     */
    public function canTransitionTo(self $new): bool
    {
        if ($this === $new || $this->isClosed()) {
            return false;
        }

        $flow = [self::PENDING, self::PROCESSING, self::SHIPPED, self::DELIVERED, self::COMPLETED];
        $position = array_search($this, $flow, true);

        return match ($new) {
            self::CANCELED => $position < array_search(self::DELIVERED, $flow, true),
            self::REFUNDED => $position >= array_search(self::DELIVERED, $flow, true),
            default => array_search($new, $flow, true) > $position,
        };
    }

    public function isClosed(): bool
    {
        return $this === self::CANCELED || $this === self::REFUNDED;
    }

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

    public function color(): string
    {
        return match ($this) {
            self::PENDING       => 'warning',
            self::PROCESSING    => 'info',
            self::SHIPPED       => 'primary',
            self::DELIVERED, self::COMPLETED => 'success',
            self::CANCELED, self::REFUNDED => 'danger',

        };
    }
}
