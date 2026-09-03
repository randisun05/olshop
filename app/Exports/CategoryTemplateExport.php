<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CategoryTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['Elektronik', '', 'ya'],
            ['Handphone', 'Elektronik', 'ya'],
        ];
    }

    public function headings(): array
    {
        return ['nama', 'kategori_induk', 'aktif'];
    }
}
