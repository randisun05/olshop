<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Midtrans = 'midtrans';
    case ManualTransfer = 'manual_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Midtrans => 'Pembayaran Online (Midtrans)',
            self::ManualTransfer => 'Transfer Bank Manual',
        };
    }
}
