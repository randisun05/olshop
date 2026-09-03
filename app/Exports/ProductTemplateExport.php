<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Rak Serbaguna 3 Susun', 'Elektronik', 'Samsung', 'Rak plastik serbaguna 3 susun.', 'RAK-001', 129000, 20, 800],
            ['Kabel Data USB-C', 'Elektronik', '', '', '', 25000, 50, 50],
        ];
    }

    public function headings(): array
    {
        return ['nama', 'kategori', 'brand', 'deskripsi', 'sku', 'harga', 'stok', 'berat'];
    }
}
