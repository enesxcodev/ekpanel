<?php

namespace Modules\Store\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Store\Database\Factories\StoreVariantFactory;

class StoreVariant extends Model
{
    /** @use HasFactory<StoreVariantFactory> */
    use HasFactory;

    protected $fillable = ['store_product_id', 'title', 'sku', 'option_values', 'price_minor', 'stock_quantity', 'is_active', 'sort_order', 'image_media_id', 'is_archived'];

    protected function casts(): array
    {
        return ['store_product_id' => 'integer', 'option_values' => 'array', 'price_minor' => 'integer', 'stock_quantity' => 'integer', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    protected static function newFactory(): StoreVariantFactory
    {
        return StoreVariantFactory::new();
    }
}
