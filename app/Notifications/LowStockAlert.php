<?php

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProductVariant $variant) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->variant->loadMissing('product');

        return [
            'title' => "Stok menipis: {$this->variant->product->name}",
            'body' => "Varian {$this->variant->label()} tersisa {$this->variant->stock}.",
            'url' => route('admin.reports.stock'),
        ];
    }
}
