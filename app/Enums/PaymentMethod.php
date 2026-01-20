<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash   = 'cash';
    case Stripe = 'stripe';
    // 

    
    public function label(): string
    {
        return match ($this) {
            self::Cash    => 'نقدى',
            self::Stripe  => 'مكتمل',
           
        };
    }

   
    public function color(): string
    {
        return match ($this) {
            self::Cash    => 'primary',
            self::Stripe  => 'success',
           

        };
    }
}
