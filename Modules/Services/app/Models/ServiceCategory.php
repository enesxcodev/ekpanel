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

    protected $fillable = ['name', 'sort_order', 'is_active', 'extra_fields'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array'];
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
