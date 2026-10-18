<?php

namespace Modules\Slider\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Slider\Database\Factories\SliderFactory;

class Slider extends Model
{
    /** @use HasFactory<SliderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title', 'subtitle', 'image', 'image_media_id', 'is_active', 'extra_fields'];

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
