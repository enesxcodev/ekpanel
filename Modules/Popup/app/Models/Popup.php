<?php

namespace Modules\Popup\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Popup\Database\Factories\PopupFactory;

class Popup extends Model
{
    /** @use HasFactory<PopupFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['image_media_id', 'is_active', 'starts_at', 'ends_at', 'repeat_policy', 'repeat_interval', 'repeat_unit'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['title', 'content', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if ($this->relationLoaded('translations')) {
                $code = request()->attributes->get('popup_language', 'tr');
                $translation = $this->getRelation('translations')->first(fn (PopupTranslation $item): bool => $item->relationLoaded('language') && $item->language?->code === $code);

                return $translation?->getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PopupTranslation::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'extra_fields' => 'array', 'image_media_id' => 'integer', 'starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime', 'repeat_interval' => 'integer'];
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
            $path = $media?->path;

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): PopupFactory
    {
        return PopupFactory::new();
    }
}
