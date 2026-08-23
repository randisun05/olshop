<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Order>  $orders
     */
    public function __construct(private readonly Collection $orders) {}

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return ['Nomor Pesanan', 'Tanggal', 'Pelanggan', 'Status', 'Subtotal', 'Diskon', 'Ongkir', 'Total'];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_at->format('Y-m-d H:i'),
            $order->user->name ?? $order->guest_name,
            $order->status->label(),
            $order->subtotal,
            $order->discount,
            $order->shipping_cost,
            $order->total,
        ];
    }
}
