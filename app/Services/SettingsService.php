<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value, update or create the record, and clear cache.
     */
    public static function set(string $key, mixed $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value ?? '']
        );

        Cache::forget("setting_{$key}");
    }

    /**
     * Clear the entire settings cache.
     */
    public static function flushCache(): void
    {
        $keys = Setting::pluck('key');

        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
    }

    /**
     * Alias for flushCache.
     */
    public static function flush(): void
    {
        static::flushCache();
    }
}
