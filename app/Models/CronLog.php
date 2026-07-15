<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CronLog extends Model {
    public $timestamps = false;

    protected $fillable = [
        'message',
        'created_at',
    ];

    public static function add($message) {
        CronLog::create([
            'message' => $message,
            'created_at' => now()
        ]);
    }
}
