<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Catat satu aksi admin kritikal (hapus/ubah data penting, verifikasi
     * pembayaran, dst). Dipanggil langsung dari controller terkait — lihat
     * docs/PERENCANAAN.md § 7 Strategi Keamanan (audit log).
     */
    public static function record(string $action, string $description, ?Model $subject = null): void
    {
        static::create([
            'user_id' => request()->user()?->id,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
