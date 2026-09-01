<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Notification;

class Order extends Model
{
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    protected $fillable = [
        'order_number',
        'user_id',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'recipient_name',
        'phone',
        'city',
        'postal_code',
        'address_line',
        'shipping_zone_name',
        'shipping_cost',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
        'paid_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function recordStatus(OrderStatus $status, ?string $note = null, ?int $changedBy = null): void
    {
        $this->update(['status' => $status]);

        $this->statusHistories()->create([
            'status' => $status->value,
            'note' => $note,
            'changed_by' => $changedBy,
        ]);
    }

    public function sendStatusNotification(): void
    {
        if ($this->user) {
            $this->user->notify(new OrderStatusUpdated($this));
        } else {
            Notification::route('mail', $this->guest_email)->notify(new OrderStatusUpdated($this));
        }
    }
}
