<?php

namespace Modules\Galeriler\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Galeriler\Database\Factories\GalerilerCategoryFactory;

class GalerilerCategory extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'name', 'icon', 'short_description', 'extra_fields'];

    protected function casts(): array
    {
        return ['parent_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name')->orderBy('id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GalerilerItem::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return list<int> */
    public function descendantIds(): array
    {
        $ids = [];
        $pending = [$this->id];
        while ($pending !== []) {
            $children = self::query()->whereIn('parent_id', $pending)->pluck('id')->all();
            $pending = array_values(array_diff($children, $ids, [$this->id]));
            $ids = [...$ids, ...$pending];
        }

        return $ids;
    }

    protected static function newFactory(): GalerilerCategoryFactory
    {
        return GalerilerCategoryFactory::new();
    }
}
