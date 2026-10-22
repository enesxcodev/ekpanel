<?php

namespace Modules\Konum\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Konum\Database\Factories\StreetFactory;

class Street extends LocationRecord
{
    protected $table = 'konum_streets';

    protected static function newFactory(): StreetFactory
    {
        return StreetFactory::new();
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
}
