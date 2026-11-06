<?php

namespace App\Enums;

use App\Models\Setting;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case VISA = 'visa';

    /**
     * The methods customers can choose. Test card payments collect no money,
     * so they are never offered in production, whatever the settings say.
     *
     * @return array<int, self>
     */
    public static function enabled(): array
    {
        return array_values(array_filter([
            Setting::get('payment_cash_enabled') ? self::CASH : null,
            Setting::get('payment_card_enabled') && ! app()->isProduction() ? self::VISA : null,
        ]));
    }

    public function label(): string
    {
        return __('app.payment_'.$this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::CASH => 'primary',
            self::VISA => 'success',
        };
    }
}
