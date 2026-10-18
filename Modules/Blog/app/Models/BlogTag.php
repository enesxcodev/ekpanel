<?php

namespace Modules\Blog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Blog\Database\Factories\BlogTagFactory;

class BlogTag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(BlogContent::class, 'blog_content_tag');
    }

    protected static function newFactory(): BlogTagFactory
    {
        return BlogTagFactory::new();
    }
}
