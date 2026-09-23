<?php

namespace App;

enum MaritalStatus: int
{
    case Single = 1;
    case Married = 2;
    case Divorced = 3;
    case Widowed = 4;

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Holost/niezamężna',
            self::Married => 'Żonaty/zamężna',
            self::Divorced => 'Rozwiedziony/rozwiedziona',
            self::Widowed => 'Wdowiec/wdowa',
        };
    }
}
