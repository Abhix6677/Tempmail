<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Domain extends Model {

    protected $fillable = [
        'domain',
        'type',
        'is_active',
    ];

    public static function getDomainsForCurrentUser() {
        $types = ['open'];
        if (Auth::user()) {
            $types = ['open', 'member'];
        }
        return self::whereIn('type', $types)->where('is_active', true)->pluck('domain')->toArray();
    }

    public static function getMemberOnlyDomains() {
        $types = ['member'];
        if (Auth::user()) {
            $types = [];
        }
        return self::whereIn('type', $types)->where('is_active', true)->pluck('domain')->toArray();
    }
}
