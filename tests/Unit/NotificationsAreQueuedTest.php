<?php

namespace Tests\Unit;

use App\Notifications\ComplaintStatusUpdated;
use App\Notifications\LowStockAlert;
use App\Notifications\NewOrderPlaced;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use ReflectionClass;
use Tests\TestCase;

/**
 * Semua notifikasi wajib di-queue supaya pengiriman email/notifikasi in-app
 * tidak memblokir siklus request (checkout, ubah status pesanan, dst)
 * menunggu SMTP. Lihat docs/DEPLOYMENT.md § 6 untuk konsekuensinya di
 * produksi (queue worker wajib jalan).
 */
class NotificationsAreQueuedTest extends TestCase
{
    public function test_order_status_updated_is_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->makeInstance(OrderStatusUpdated::class));
    }

    public function test_complaint_status_updated_is_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->makeInstance(ComplaintStatusUpdated::class));
    }

    public function test_new_order_placed_is_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->makeInstance(NewOrderPlaced::class));
    }

    public function test_low_stock_alert_is_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, $this->makeInstance(LowStockAlert::class));
    }

    private function makeInstance(string $class): object
    {
        return (new ReflectionClass($class))->newInstanceWithoutConstructor();
    }
}
