<?php

namespace Modules\Store\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Database\Factories\StoreProductFactory;

class StoreProduct extends Model
{
    /** @use HasFactory<StoreProductFactory> */
    use HasFactory;

    protected $fillable = ['store_category_id', 'title', 'slug', 'sku', 'short_description', 'content', 'price_minor', 'compare_at_price_minor', 'currency', 'tracks_stock', 'stock_quantity', 'low_stock_threshold', 'allow_backorders', 'is_active', 'sort_order', 'extra_fields', 'image', 'image_media_id', 'share_image', 'share_image_media_id', 'meta_title', 'meta_description', 'has_variants', 'option_groups', 'source_product_id', 'weight_grams', 'width_cm', 'height_cm', 'length_cm', 'preparation_days', 'delivery_description', 'is_archived'];

    protected function casts(): array
    {
        return ['has_variants' => 'boolean', 'option_groups' => 'array', 'is_archived' => 'boolean', 'store_category_id' => 'integer', 'price_minor' => 'integer', 'compare_at_price_minor' => 'integer', 'tracks_stock' => 'boolean', 'stock_quantity' => 'integer', 'low_stock_threshold' => 'integer', 'allow_backorders' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'extra_fields' => 'array'];
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
