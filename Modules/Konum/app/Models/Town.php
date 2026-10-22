<?php

namespace Modules\Konum\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Konum\Database\Factories\TownFactory;

class Town extends LocationRecord
{
    protected $table = 'konum_towns';

    protected static function newFactory(): TownFactory
    {
        return TownFactory::new();
    }

    public function districtRecords(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
