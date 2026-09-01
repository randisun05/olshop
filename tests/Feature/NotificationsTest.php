<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Enums\OrderStatus;
use App\Models\Complaint;
use App\Models\Order;
use App\Notifications\ComplaintStatusUpdated;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresi untuk bug nyata yang ditemukan saat smoke test Fase 7: tabel
 * `notifications` tidak pernah dimigrasikan meski OrderStatusUpdated dan
 * ComplaintStatusUpdated memakai channel 'database' untuk User. Selama ini
 * lolos karena semua test order/pembayaran memakai Notification::fake(),
 * jadi write sungguhan ke tabel tidak pernah teruji. Test ini SENGAJA tidak
 * fake Notification, supaya path database channel benar-benar dieksekusi.
 */
class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_notification_is_actually_persisted_for_registered_user(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Paid]);

        $order->recordStatus(OrderStatus::Processing, 'Diproses.');
        $order->sendStatusNotification();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'notifiable_type' => $customer->getMorphClass(),
            'type' => OrderStatusUpdated::class,
        ]);
    }

    public function test_complaint_status_notification_is_actually_persisted(): void
    {
        $complaint = Complaint::factory()->create();

        $complaint->updateStatus(ComplaintStatus::Resolved, 'Selesai diproses.');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $complaint->user_id,
            'notifiable_type' => $complaint->user->getMorphClass(),
            'type' => ComplaintStatusUpdated::class,
        ]);
    }
}
