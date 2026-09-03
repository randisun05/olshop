<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class NewOrderPlaced extends Notification
{
    public function __construct(public Order $order) {}

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
        return [
            'title' => "Pesanan baru {$this->order->order_number}",
            'body' => 'Total Rp'.number_format((float) $this->order->total, 0, ',', '.'),
            'url' => route('admin.orders.show', $this->order),
        ];
    }
}
