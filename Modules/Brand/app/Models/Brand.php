<?php

namespace Modules\Brand\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Brand\Database\Factories\BrandFactory;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    protected $fillable = ['name', 'image', 'short_description', 'link', 'extra_fields'];

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }
}
