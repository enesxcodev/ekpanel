<?php

namespace Modules\Konum\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class LocationRecord extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $incrementing = false;

    protected $hidden = ['polygons', 'towns', 'districts', 'neighbourhoods', 'streets', 'boundingbox', 'source_row_hash'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_consistent' => 'boolean', 'lat' => 'double', 'lon' => 'double', 'boundingbox' => 'array', 'towns' => 'array', 'districts' => 'array', 'neighbourhoods' => 'array', 'streets' => 'array'];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
