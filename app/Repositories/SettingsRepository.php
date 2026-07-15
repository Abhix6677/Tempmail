<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsRepository
{
    /**
     * Get all settings (cached)
     */
    public static function all()
    {
        return Cache::remember('settings.all', 600, function () {
            return Setting::all()->keyBy('key');
        });
    }

    /**
     * Get single setting value (auto-unserialize)
     */
    public static function get(string $key, $default = null)
    {
        $settings = self::all();

        if (!isset($settings[$key])) {
            return $default;
        }

        $value = $settings[$key]->value;

        try {
            return @unserialize($value) !== false || $value === 'b:0;'
                ? unserialize($value)
                : $value;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * Clear cached settings
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');
    }
}
