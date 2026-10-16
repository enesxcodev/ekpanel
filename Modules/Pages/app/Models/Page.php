<?php

namespace Modules\Pages\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Pages\Database\Factories\PageFactory;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $fillable = ['page_category_id', 'title', 'short_description', 'content', 'image', 'slug', 'status', 'sort_order', 'extra_fields'];

    protected $attributes = ['status' => 'published'];

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }

    public function getUrlPathAttribute(): string
    {
        return $this->category === null ? $this->slug : trim($this->category->url_path.'/'.$this->slug, '/');
    }

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
