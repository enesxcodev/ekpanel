<?php

namespace Modules\Slider\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Slider\Database\Factories\SliderFactory;

class Slider extends Model
{
    /** @use HasFactory<SliderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['image', 'image_media_id', 'is_active'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['title', 'subtitle', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if ($this->relationLoaded('translations')) {
                $code = request()->attributes->get('slider_language', 'tr');
                $translation = $this->getRelation('translations')->first(fn (SliderTranslation $item): bool => $item->relationLoaded('language') && $item->language?->code === $code);

                return $translation?->getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SliderTranslation::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'extra_fields' => 'array', 'image_media_id' => 'integer'];
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    /** @return Attribute<?string, never> */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->imageMedia;
            $path = $media?->path ?? $this->image;

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): SliderFactory
    {
        return SliderFactory::new();
    }
}
