<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'keywords',
        'category',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Kata kunci disimpan sebagai satu string dipisah koma (mis. "resi,
     * lacak, kirim") supaya mudah diisi admin lewat satu input teks biasa.
     *
     * @return array<int, string>
     */
    public function keywordList(): array
    {
        return collect(explode(',', $this->keywords))
            ->map(fn (string $k) => mb_strtolower(trim($k)))
            ->filter(fn (string $k) => $k !== '')
            ->values()
            ->all();
    }
}
