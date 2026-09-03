<?php

namespace App\Enums;

enum StockAdjustmentType: string
{
    case OrderOut = 'order_out';
    case OrderRestock = 'order_restock';
    case ManualIn = 'manual_in';
    case ManualOut = 'manual_out';

    public function label(): string
    {
        return match ($this) {
            self::OrderOut => 'Keluar (Pesanan)',
            self::OrderRestock => 'Dikembalikan (Pesanan Batal/Gagal)',
            self::ManualIn => 'Penyesuaian Manual (Tambah)',
            self::ManualOut => 'Penyesuaian Manual (Kurang)',
        };
    }
}
