<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Store\Database\Factories\StoreOrderLineFactory;

class StoreOrderLine extends Model
{
    use HasFactory;

    protected $fillable = ['store_order_id', 'store_product_id', 'store_variant_id', 'product_title', 'variant_title', 'sku', 'unit_price_minor', 'quantity', 'line_total_minor', 'snapshot'];

    protected function casts(): array
    {
        return ['store_order_id' => 'integer', 'store_product_id' => 'integer', 'store_variant_id' => 'integer', 'unit_price_minor' => 'integer', 'quantity' => 'integer', 'line_total_minor' => 'integer', 'snapshot' => 'array'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(StoreOrder::class, 'store_order_id');
    }

    // protected static function newFactory(): StoreOrderLineFactory
    // {
    //     // return StoreOrderLineFactory::new();
    // }
}
