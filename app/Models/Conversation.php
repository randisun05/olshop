<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'assigned_to',
        'subject',
        'status',
        'customer_read_at',
        'staff_read_at',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConversationStatus::class,
            'customer_read_at' => 'datetime',
            'staff_read_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('id');
    }

    public function unreadCountFor(string $side): int
    {
        $readAt = $side === 'customer' ? $this->customer_read_at : $this->staff_read_at;
        $senderColumn = $side === 'customer' ? '!=' : '=';

        return $this->messages()
            ->where('sender_id', $senderColumn, $this->user_id)
            ->when($readAt, fn ($q) => $q->where('created_at', '>', $readAt), fn ($q) => $q)
            ->count();
    }

    public function markReadBy(string $side): void
    {
        $this->update([$side === 'customer' ? 'customer_read_at' : 'staff_read_at' => now()]);
    }

    /**
     * Jumlah pesan belum dibaca di seluruh percakapan milik pelanggan ini
     * (dipakai untuk badge notifikasi di CustomerLayout).
     */
    public static function unreadCountForCustomer(User $user): int
    {
        return static::query()
            ->join('chat_messages', 'chat_messages.conversation_id', '=', 'conversations.id')
            ->where('conversations.user_id', $user->id)
            ->where('chat_messages.sender_id', '!=', $user->id)
            ->where(function ($q) {
                $q->whereNull('conversations.customer_read_at')
                    ->orWhereColumn('chat_messages.created_at', '>', 'conversations.customer_read_at');
            })
            ->count();
    }

    /**
     * Jumlah pesan belum dibaca di seluruh percakapan (dipakai untuk badge
     * notifikasi Staff CS/Admin di AdminLayout — kotak masuk bersama).
     */
    public static function unreadCountForStaff(): int
    {
        return static::query()
            ->join('chat_messages', 'chat_messages.conversation_id', '=', 'conversations.id')
            ->whereColumn('chat_messages.sender_id', '=', 'conversations.user_id')
            ->where(function ($q) {
                $q->whereNull('conversations.staff_read_at')
                    ->orWhereColumn('chat_messages.created_at', '>', 'conversations.staff_read_at');
            })
            ->count();
    }
}
