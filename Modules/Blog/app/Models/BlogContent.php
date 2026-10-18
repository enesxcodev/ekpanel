<?php

namespace Modules\Blog\Models;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\Database\Factories\BlogContentFactory;

class BlogContent extends Model
{
    use HasFactory;

    protected $fillable = ['blog_category_id', 'author_id', 'title', 'slug', 'meta_title', 'meta_description', 'short_description', 'content', 'image', 'image_media_id', 'status', 'published_at', 'extra_fields'];

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return ['blog_category_id' => 'integer', 'author_id' => 'integer', 'image_media_id' => 'integer', 'published_at' => 'datetime', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_content_tag');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->imageMedia;
            $path = $media?->path ?? ($this->image_media_id ? null : $this->image);

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): BlogContentFactory
    {
        return BlogContentFactory::new();
    }
}
