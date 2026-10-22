<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Store\Database\Factories\StockMovementFactory;

class StockMovement extends Model
{
    protected $table = 'store_stock_movements';

    use HasFactory;

    protected $fillable = ['store_product_id', 'store_variant_id', 'quantity', 'resulting_quantity', 'reason', 'reference_type', 'reference_id', 'admin_id'];

    protected function casts(): array
    {
        return ['store_product_id' => 'integer', 'store_variant_id' => 'integer', 'quantity' => 'integer', 'resulting_quantity' => 'integer', 'admin_id' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(StoreVariant::class, 'store_variant_id');
    }

    // protected static function newFactory(): StockMovementFactory
    // {
    //     // return StockMovementFactory::new();
    // }
}
