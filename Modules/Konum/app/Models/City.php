<?php

namespace Modules\Konum\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Konum\Database\Factories\CityFactory;

class City extends LocationRecord
{
    protected $table = 'konum_cities';

    protected static function newFactory(): CityFactory
    {
        return CityFactory::new();
    }

    public function townRecords(): HasMany
    {
        return $this->hasMany(Town::class);
    }
}
