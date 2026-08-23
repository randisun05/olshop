<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::allCached()->get($key, $default);
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    /**
     * Dicache sebagai array murni (bukan objek Collection) agar tahan
     * terhadap kegagalan unserialize pada cache driver berbasis file.
     *
     * @return Collection<string, ?string>
     */
    public static function allCached(): Collection
    {
        $values = Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());

        return collect(is_array($values) ? $values : []);
    }
}
