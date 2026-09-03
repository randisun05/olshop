<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplaintStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Complaint $complaint) {}

    /**
     * @return string[]
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $complaint = $this->complaint;

        $mail = (new MailMessage)
            ->subject("Update {$complaint->type->label()} — Pesanan {$complaint->order->order_number}")
            ->greeting('Halo, '.$notifiable->name.'!')
            ->line("Pengajuan {$complaint->type->label()} Anda untuk pesanan {$complaint->order->order_number} telah diperbarui menjadi: **{$complaint->status->label()}**.");

        if ($complaint->admin_note) {
            $mail->line("Catatan dari tim kami: {$complaint->admin_note}");
        }

        return $mail->line('Terima kasih atas kesabaran Anda.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'order_number' => $this->complaint->order->order_number,
            'status' => $this->complaint->status->value,
            'status_label' => $this->complaint->status->label(),
        ];
    }
}
