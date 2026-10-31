<?php

namespace Modules\Products\Models;

use App\Models\Forms\SiteForm;
use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Products\Database\Factories\ProductFactory;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['product_category_id', 'sort_order', 'image', 'image_media_id', 'site_form_id', 'is_active'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['title', 'slug', 'short_description', 'content', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            $translations = $this->relationLoaded('translations') ? $this->getRelation('translations') : $this->translations;
            $languageId = request()->attributes->get('products_language_id');
            $translation = $translations->first(fn (ProductTranslation $item): bool => (int) $item->lang_id === (int) $languageId);

            return $translation?->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(MediaFile::class, 'product_images')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(SiteForm::class, 'site_form_id');
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

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
