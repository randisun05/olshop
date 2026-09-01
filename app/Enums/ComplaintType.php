<?php

namespace App\Enums;

enum ComplaintType: string
{
    case Retur = 'retur';
    case Komplain = 'komplain';

    public function label(): string
    {
        return match ($this) {
            self::Retur => 'Retur/Pengembalian',
            self::Komplain => 'Komplain Lainnya',
        };
    }
}
