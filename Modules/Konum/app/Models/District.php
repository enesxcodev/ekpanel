<?php

namespace Modules\Konum\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Konum\Database\Factories\DistrictFactory;

class District extends LocationRecord
{
    protected $table = 'konum_districts';

    protected static function newFactory(): DistrictFactory
    {
        return DistrictFactory::new();
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function neighbourhoodRecords(): HasMany
    {
        return $this->hasMany(Neighbourhood::class);
    }
}
