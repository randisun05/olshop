<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TopProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, object{product_name: string, qty_sold: int, revenue: float}>  $rows
     */
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['Produk', 'Jumlah Terjual', 'Total Pendapatan'];
    }

    public function map($row): array
    {
        return [
            $row->product_name,
            $row->qty_sold,
            $row->revenue,
        ];
    }
}
