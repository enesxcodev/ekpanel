<?php

namespace Modules\Services\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Services\Database\Factories\ServiceFactory;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $with = ['translations.language'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if (! $this->relationLoaded('translations')) {
                return null;
            }
            $code = request()->attributes->get('services_language', 'tr');

            return $this->getRelation('translations')->first(fn ($item): bool => $item->relationLoaded('language') && $item->language?->code === $code)?->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    protected $fillable = ['service_category_id', 'sort_order', 'image', 'image_media_id', 'icon', 'is_active'];

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
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

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }
}
