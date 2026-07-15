<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Setting extends Model {
    protected $fillable = [
        'value',
    ];

    public static function pick($key) {
        try {
            if (Schema::hasTable((new Setting)->getTable())) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    return unserialize($setting->value);
                }
            }
        } catch (\Throwable $e) {
            // During installation the DB may not be ready yet
            return null;
        }
        return false;
    }

    public static function put($key, $value) {
        if (Schema::hasTable((new Setting)->getTable())) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->value = serialize($value);
                return $setting->save();
            }
        }
        return false;
    }
}
