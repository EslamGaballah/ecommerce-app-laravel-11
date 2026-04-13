<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH   = 'cash';
    case VISA = 'visa';
    // 

    
    public function label(): string
    {
        return match ($this) {
            self::CASH    => 'نقدى',
            self::VISA  => 'فيزا',
           
        };
    }

   
    public function color(): string
    {
        return match ($this) {
            self::CASH    => 'primary',
            self::VISA  => 'success',
           

        };
    }
}
