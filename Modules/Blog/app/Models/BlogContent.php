<?php

namespace Modules\Blog\Models;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\Database\Factories\BlogContentFactory;

class BlogContent extends Model
{
    use HasFactory;

    protected $with = ['translations.language'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
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

    protected $fillable = ['blog_category_id', 'author_id', 'image', 'image_media_id', 'status', 'published_at'];

    protected $attributes = ['status' => 'draft'];

    public function translations(): HasMany
    {
        return $this->hasMany(BlogContentTranslation::class);
    }

    protected function casts(): array
    {
        return ['blog_category_id' => 'integer', 'author_id' => 'integer', 'image_media_id' => 'integer', 'published_at' => 'datetime'];
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
