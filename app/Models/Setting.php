<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** Kunci yang disimpan terenkripsi (jangan ganti APP_KEY setelah diisi). */
    public const RAHASIA = ['kirim_api_key', 'smtp_password'];

    public static function semua(): array
    {
        return Cache::rememberForever('settings', fn() => static::pluck('value', 'key')->all());
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = static::semua()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        if (in_array($key, self::RAHASIA, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable) {
                return $default;
            }
        }

        return $value;
    }

    public static function bool(string $key, bool $default = false): bool
    {
        return in_array(strtolower((string) static::get($key, $default ? '1' : '0')), ['1', 'true'], true);
    }

    public static function set(string $key, mixed $value): void
    {
        if ($value !== null && $value !== '' && in_array($key, self::RAHASIA, true)) {
            $value = Crypt::encryptString((string) $value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }
}
