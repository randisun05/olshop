<?php

namespace App\Notifications;

use App\Models\Order;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    public function __construct(public Order $order) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return $notifiable instanceof User ? ['mail', 'database'] : ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;

        $mail = (new MailMessage)
            ->subject("Update Pesanan {$order->order_number} — {$order->status->label()}")
            ->greeting('Halo, '.($order->user?->name ?? $order->guest_name).'!')
            ->line("Status pesanan Anda {$order->order_number} telah diperbarui menjadi: **{$order->status->label()}**.");

        if ($order->shipment && $order->status->value === 'shipped') {
            $mail->line("Kurir: {$order->shipment->courier}")
                ->line("Nomor Resi: {$order->shipment->tracking_number}");
        }

        return $mail->line('Total pesanan: Rp'.number_format((float) $order->total, 0, ',', '.'))
            ->line('Terima kasih telah berbelanja di toko kami.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_number' => $this->order->order_number,
            'status' => $this->order->status->value,
            'status_label' => $this->order->status->label(),
        ];
    }
}
