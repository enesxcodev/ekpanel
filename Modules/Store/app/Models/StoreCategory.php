<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Store\Database\Factories\StoreCategoryFactory;

class StoreCategory extends Model
{
    /** @use HasFactory<StoreCategoryFactory> */
    use HasFactory;

    protected $fillable = ['name', 'slug', 'sort_order', 'is_active', 'extra_fields'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(StoreProduct::class);
    }

    protected static function newFactory(): StoreCategoryFactory
    {
        return StoreCategoryFactory::new();
    }
}
