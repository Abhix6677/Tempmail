<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model {

    protected $fillable = [
        'title',
        'content',
        'image',
        'slug',
        'meta',
        'header',
        'is_published',
        'excerpt',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function categories(): BelongsToMany {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Get all translations for this post.
     */
    public function translations(): HasMany {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', 'post');
    }

    /**
     * Get a specific translation for this post.
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
     * Check if the post has a translation for a specific language.
     */
    public function hasTranslation(string $language): bool {
        return $this->translations()->where('language', $language)->exists();
    }
}
