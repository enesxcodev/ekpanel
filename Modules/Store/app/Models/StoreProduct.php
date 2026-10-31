<?php

namespace Modules\Store\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Database\Factories\StoreProductFactory;
use Modules\Store\Services\StoreLanguageService;

class StoreProduct extends Model
{
    /** @use HasFactory<StoreProductFactory> */
    use HasFactory;

    protected $fillable = ['store_category_id', 'sku', 'price_minor', 'compare_at_price_minor', 'currency', 'tracks_stock', 'stock_quantity', 'low_stock_threshold', 'allow_backorders', 'is_active', 'sort_order', 'image', 'image_media_id', 'share_image', 'share_image_media_id', 'has_variants', 'option_groups', 'source_product_id', 'weight_grams', 'width_cm', 'height_cm', 'length_cm', 'preparation_days', 'is_archived'];

    protected function casts(): array
    {
        return ['has_variants' => 'boolean', 'option_groups' => 'array', 'is_archived' => 'boolean', 'store_category_id' => 'integer', 'price_minor' => 'integer', 'compare_at_price_minor' => 'integer', 'tracks_stock' => 'boolean', 'stock_quantity' => 'integer', 'low_stock_threshold' => 'integer', 'allow_backorders' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StoreProductTranslation::class, 'store_product_id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(StoreProductTranslation::class, 'store_product_id')->where('lang_id', request()->attributes->get('store_language_id', app(StoreLanguageService::class)->forFrontend()?->id));
    }

    protected function title(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->title);
    }

    protected function slug(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->slug);
    }

    protected function shortDescription(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->short_description);
    }

    protected function content(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->content);
    }

    protected function metaTitle(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->meta_title);
    }

    protected function metaDescription(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->meta_description);
    }

    protected function deliveryDescription(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->delivery_description);
    }

    protected function extraFields(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->extra_fields ?? []);
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(MediaFile::class, 'store_product_images')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    public function shareImageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'share_image_media_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->imageMedia ? Storage::disk($this->imageMedia->disk)->url($this->imageMedia->path) : ($this->image ? Storage::disk('public')->url($this->image) : null));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(StoreVariant::class)->orderBy('sort_order')->orderBy('id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    protected static function newFactory(): StoreProductFactory
    {
        return StoreProductFactory::new();
    }
}
