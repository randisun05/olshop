<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Peninjauan',
            self::Processing => 'Sedang Diproses',
            self::Resolved => 'Selesai',
            self::Rejected => 'Ditolak',
        };
    }

    /**
     * Status yang masih dianggap "terbuka" — dipakai untuk mencegah pelanggan
     * mengajukan komplain baru untuk pesanan yang sama selagi satu masih berjalan.
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Processing], true);
    }
}
