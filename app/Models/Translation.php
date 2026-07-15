<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model {
    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'language',
        'title',
        'content',
        'meta',
        'header',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the parent translatable model (Post or Page).
     */
    public function translatable(): MorphTo {
        return $this->morphTo();
    }
}
