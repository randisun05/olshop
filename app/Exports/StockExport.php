<?php

namespace App\Exports;

use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, ProductVariant>  $variants
     */
    public function __construct(private readonly Collection $variants) {}

    public function collection(): Collection
    {
        return $this->variants;
    }

    public function headings(): array
    {
        return ['Produk', 'Varian', 'SKU', 'Harga', 'Stok'];
    }

    public function map($variant): array
    {
        return [
            $variant->product->name,
            $variant->label(),
            $variant->sku,
            $variant->price,
            $variant->stock,
        ];
    }
}
