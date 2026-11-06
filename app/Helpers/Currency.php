<?php

namespace App\Helpers;

use App\Models\Setting;
use NumberFormatter;

class Currency
{
    public function __invoke(...$params)
    {
        return static::format(...$params);
    }

    /**
     * Prices are stored as plain numbers in the store's currency, so nothing is converted here.
     */
    public static function format($amount, $currency = null)
    {
        $formatter = new NumberFormatter(config('app.locale'), NumberFormatter::CURRENCY);

        return $formatter->formatCurrency((float) $amount, $currency ?? Setting::get('currency'));
    }
}
