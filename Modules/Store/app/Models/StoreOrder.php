<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Store\Database\Factories\StoreOrderFactory;

class StoreOrder extends Model
{
    /** @use HasFactory<StoreOrderFactory> */
    use HasFactory;

    protected $fillable = ['number', 'user_id', 'customer_name', 'customer_email', 'shipping_address', 'currency', 'subtotal_minor', 'tax_minor', 'shipping_minor', 'discount_minor', 'total_minor', 'status', 'payment_status', 'stock_released_at', 'status_history'];

    protected function casts(): array
    {
        return ['user_id' => 'integer', 'shipping_address' => 'array', 'subtotal_minor' => 'integer', 'tax_minor' => 'integer', 'shipping_minor' => 'integer', 'discount_minor' => 'integer', 'total_minor' => 'integer', 'stock_released_at' => 'datetime', 'status_history' => 'array'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(StoreOrderLine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    protected static function newFactory(): StoreOrderFactory
    {
        return StoreOrderFactory::new();
    }
}
