<?php

namespace Modules\Branches\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Branches\Database\Factories\BranchFactory;
use Modules\Konum\Models\City;
use Modules\Konum\Models\District;
use Modules\Konum\Models\Neighbourhood;
use Modules\Konum\Models\Street;
use Modules\Konum\Models\Town;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_category_id', 'name', 'city_id', 'town_id', 'district_id', 'neighbourhood_id', 'street_id',
        'address_detail', 'phone', 'email', 'website', 'working_hours', 'map_url', 'latitude', 'longitude',
        'is_active', 'sort_order', 'extra_fields',
    ];

    protected function casts(): array
    {
        return [
            'branch_category_id' => 'integer', 'city_id' => 'integer', 'town_id' => 'integer', 'district_id' => 'integer',
            'neighbourhood_id' => 'integer', 'street_id' => 'integer', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7',
            'sort_order' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array',
        ];
    }

    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BranchCategory::class, 'branch_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(BranchTranslation::class);
    }

    public function translation(): HasMany
    {
        return $this->translations();
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function neighbourhood(): BelongsTo
    {
        return $this->belongsTo(Neighbourhood::class);
    }

    public function street(): BelongsTo
    {
        return $this->belongsTo(Street::class);
    }
}
