<?php

namespace Modules\Brand\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Database\Factories\BrandFactory;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    protected $fillable = ['sort_order', 'is_active', 'image', 'image_media_id', 'link'];

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BrandTranslation::class);
    }

    public function selectedTranslation(): HasOne
    {
        return $this->hasOne(BrandTranslation::class, 'brand_id')->where('lang_id', request()->attributes->get('brand_language_id'));
    }

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'short_description', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            $translation = $this->relationLoaded('selectedTranslation') ? $this->getRelation('selectedTranslation') : null;

            return $translation?->getAttribute($key);
        }

        return parent::getAttribute($key);
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

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }
}
