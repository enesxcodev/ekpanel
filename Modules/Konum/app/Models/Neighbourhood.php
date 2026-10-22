<?php

namespace Modules\Konum\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Konum\Database\Factories\NeighbourhoodFactory;

class Neighbourhood extends LocationRecord
{
    protected $table = 'konum_neighbourhoods';

    protected static function newFactory(): NeighbourhoodFactory
    {
        return NeighbourhoodFactory::new();
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function streetRecords(): HasMany
    {
        return $this->hasMany(Street::class);
    }
}
