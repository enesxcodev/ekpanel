<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Services\Database\Factories\ServiceCategoryFactory;

class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $with = ['translations.language'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if (! $this->relationLoaded('translations')) {
                return null;
            }
            $code = request()->attributes->get('services_language', 'tr');

            return $this->getRelation('translations')->first(fn ($item): bool => $item->relationLoaded('language') && $item->language?->code === $code)?->getAttribute($key);
        }

        return parent::getAttribute($key);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ServiceCategoryTranslation::class);
    }

    protected $fillable = ['sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    protected static function newFactory(): ServiceCategoryFactory
    {
        return ServiceCategoryFactory::new();
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }
}
