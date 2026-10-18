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

    protected $fillable = ['parent_id', 'name', 'slug', 'sort_order', 'extra_fields'];

    protected function casts(): array
    {
        return ['parent_id' => 'integer', 'sort_order' => 'integer', 'extra_fields' => 'array'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
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
