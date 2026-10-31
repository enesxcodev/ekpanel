<?php

namespace Modules\Testimonials\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Testimonials\Database\Factories\TestimonialFactory;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = ['image', 'image_media_id', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer', 'extra_fields' => 'array'];
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TestimonialTranslation::class);
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->imageMedia;
            $path = $media?->path ?? ($this->image_media_id ? null : $this->image);

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): TestimonialFactory
    {
        return TestimonialFactory::new();
    }
}
