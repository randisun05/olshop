<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\NewOrderPlaced;
use Illuminate\Notifications\Notification;

class StockNotifier
{
    public static function notifyNewOrder(Order $order): void
    {
        static::notifyByPermission('orders.manage', new NewOrderPlaced($order));
    }

    public static function checkLowStock(ProductVariant $variant, int $stockBefore): void
    {
        $threshold = (int) (Setting::get('low_stock_threshold') ?? 5);
        $stockAfter = $variant->fresh()->stock;

        if ($stockBefore > $threshold && $stockAfter <= $threshold) {
            static::notifyByPermission('products.manage', new LowStockAlert($variant));
        }
    }

    private static function notifyByPermission(string $permission, Notification $notification): void
    {
        User::where('is_active', true)
            ->whereHas('roles')
            ->get()
            ->filter(fn (User $user) => $user->can($permission))
            ->each
            ->notify($notification);
    }
}
