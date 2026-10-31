<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Blog\Database\Factories\BlogCategoryFactory;

class BlogCategory extends Model
{
    use HasFactory;

    protected $with = ['translations.language'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'slug', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if (! $this->relationLoaded('translations')) {
                return null;
            }
            $code = request()->attributes->get('blog_language', 'tr');
            $translation = $this->getRelation('translations')->first(fn ($item): bool => $item->relationLoaded('language') && $item->language?->code === $code);

            return $translation?->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    protected $fillable = ['parent_id', 'sort_order'];

    public function translations(): HasMany
    {
        return $this->hasMany(BlogCategoryTranslation::class);
    }

    protected function casts(): array
    {
        return ['parent_id' => 'integer', 'sort_order' => 'integer'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BlogContent::class, 'blog_category_id');
    }

    public function getPathAttribute(): string
    {
        $parts = [$this->name];
        $parent = $this->parent;
        while ($parent !== null) {
            array_unshift($parts, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' / ', $parts);
    }

    protected static function newFactory(): BlogCategoryFactory
    {
        return BlogCategoryFactory::new();
    }

    /** @return list<int> */
    public function descendantIds(): array
    {
        $ids = [];
        $pending = [$this->id];
        while ($pending !== []) {
            $children = self::query()->whereIn('parent_id', $pending)->pluck('id')->all();
            $children = array_values(array_diff($children, $ids, [$this->id]));
            $ids = [...$ids, ...$children];
            $pending = $children;
        }

        return $ids;
    }
}
