<?php

namespace Modules\Pages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Pages\Database\Factories\PageCategoryFactory;

class PageCategory extends Model
{
    /** @use HasFactory<PageCategoryFactory> */
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'short_description', 'slug', 'sort_order', 'extra_fields'];

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class)->orderBy('sort_order')->orderBy('title');
    }

    public function getUrlPathAttribute(): string
    {
        $parts = [$this->slug];
        $parent = $this->parent;
        while ($parent !== null) {
            array_unshift($parts, $parent->slug);
            $parent = $parent->parent;
        }

        return implode('/', $parts);
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

    protected static function newFactory(): PageCategoryFactory
    {
        return PageCategoryFactory::new();
    }
}
