<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Products\Database\Factories\ProductCategoryFactory;

class ProductCategory extends Model
{
    /** @use HasFactory<ProductCategoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['sort_order', 'is_active'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            $translations = $this->relationLoaded('translations') ? $this->getRelation('translations') : $this->translations;
            $languageId = request()->attributes->get('products_language_id');
            $translation = $translations->first(fn (ProductCategoryTranslation $item): bool => (int) $item->lang_id === (int) $languageId);

            return $translation?->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    protected static function newFactory(): ProductCategoryFactory
    {
        return ProductCategoryFactory::new();
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductCategoryTranslation::class);
    }
}
