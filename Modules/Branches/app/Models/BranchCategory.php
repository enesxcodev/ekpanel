<?php

namespace Modules\Branches\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Branches\Database\Factories\BranchCategoryFactory;

class BranchCategory extends Model
{
    use HasFactory;

    protected $fillable = ['slug', 'name', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    protected static function newFactory(): BranchCategoryFactory
    {
        return BranchCategoryFactory::new();
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BranchCategoryTranslation::class);
    }
}
