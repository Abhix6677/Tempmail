<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class Setting extends Model {
    protected $fillable = [
        'value',
    ];

    public static function safeUnserialize($value) {
        if ($value === 'b:0;' || $value === serialize(false)) {
            return false;
        }
        $unserialized = @unserialize($value);
        if ($unserialized !== false) {
            return $unserialized;
        }
        return $value;
    }

    public static function pick($key) {
        try {
            // First check config if already populated by middleware
            $fromConfig = config('app.settings.' . $key);
            if ($fromConfig !== null) {
                return $fromConfig;
            }

            $settings = Cache::remember('tmail_settings_key_values', 3600, function () {
                if (!Schema::hasTable((new Setting)->getTable())) {
                    return [];
                }
                $records = Setting::all();
                $arr = [];
                foreach ($records as $option) {
                    $arr[$option->key] = self::safeUnserialize($option->value);
                }
                return $arr;
            });

            return $settings[$key] ?? false;
        } catch (\Throwable $e) {
            // During installation the DB may not be ready yet
            return null;
        }
    }

    public static function put($key, $value) {
        Cache::forget('tmail_settings_key_values');
        if (class_exists('\App\Repositories\SettingsRepository')) {
            \App\Repositories\SettingsRepository::clearCache();
        }
        if (Schema::hasTable((new Setting)->getTable())) {
            $setting = Setting::firstOrNew(['key' => $key]);
            if (!$setting->exists) {
                $setting->key = $key;
            }
            $setting->value = serialize($value);
            $res = $setting->save();
            config(['app.settings.' . $key => $value]);
            return $res;
        }
        return false;
    }
}
