<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'slug',
        'meta',
        'header',
        'is_published',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get all translations for this page.
     */
    public function translations(): HasMany {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', 'page');
    }

    /**
     * Get a specific translation for this page.
     */
    public function translation(string $language): ?Translation {
        return $this->translations()->where('language', $language)->first();
    }

    /**
     * Get the translated content for a specific language.
     */
    public function getTranslatedContent(string $language): ?array {
        $translation = $this->translation($language);

        if (!$translation) {
            return null;
        }

        return [
            'title' => $translation->title,
            'content' => $translation->content,
            'meta' => $translation->meta,
            'header' => $translation->header,
        ];
    }

    /**
     * Check if the page has a translation for a specific language.
     */
    public function hasTranslation(string $language): bool {
        return $this->translations()->where('language', $language)->exists();
    }
}
