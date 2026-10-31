<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Store\Database\Factories\StoreCategoryFactory;
use Modules\Store\Services\StoreLanguageService;

class StoreCategory extends Model
{
    /** @use HasFactory<StoreCategoryFactory> */
    use HasFactory;

    protected $fillable = ['sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(StoreCategoryTranslation::class, 'store_category_id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(StoreCategoryTranslation::class, 'store_category_id')->where('lang_id', request()->attributes->get('store_language_id', app(StoreLanguageService::class)->forFrontend()?->id));
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->name);
    }

    protected function slug(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->slug);
    }

    protected function extraFields(): Attribute
    {
        return Attribute::get(fn () => $this->translation?->extra_fields ?? []);
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
