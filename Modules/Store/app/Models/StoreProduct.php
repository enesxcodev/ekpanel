<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Store\Database\Factories\StoreProductFactory;

class StoreProduct extends Model
{
    /** @use HasFactory<StoreProductFactory> */
    use HasFactory;

    protected $fillable = ['store_category_id', 'title', 'slug', 'sku', 'short_description', 'content', 'price_minor', 'compare_at_price_minor', 'currency', 'tracks_stock', 'stock_quantity', 'low_stock_threshold', 'allow_backorders', 'is_active', 'sort_order', 'extra_fields'];

    protected function casts(): array
    {
        return ['store_category_id' => 'integer', 'price_minor' => 'integer', 'compare_at_price_minor' => 'integer', 'tracks_stock' => 'boolean', 'stock_quantity' => 'integer', 'low_stock_threshold' => 'integer', 'allow_backorders' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer', 'extra_fields' => 'array'];
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
