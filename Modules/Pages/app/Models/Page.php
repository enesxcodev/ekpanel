<?php

namespace Modules\Pages\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Pages\Database\Factories\PageFactory;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $fillable = ['page_category_id', 'title', 'meta_title', 'meta_keywords', 'meta_description', 'short_description', 'content', 'image', 'image_media_id', 'slug', 'status', 'sort_order', 'extra_fields'];

    protected $attributes = ['status' => 'published'];

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class, 'page_category_id');
    }

    public function getUrlPathAttribute(): string
    {
        return $this->category === null ? $this->slug : trim($this->category->url_path.'/'.$this->slug, '/');
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

    protected static function newFactory(): PageFactory
    {
        return PageFactory::new();
    }
}
