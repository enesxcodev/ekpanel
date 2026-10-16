<?php

namespace Modules\Sector\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Sector\Database\Factories\SectorFactory;

class Sector extends Model
{
    /** @use HasFactory<SectorFactory> */
    use HasFactory;

    protected $fillable = ['title', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'image', 'is_active', 'extra_fields'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    protected static function newFactory(): SectorFactory
    {
        return SectorFactory::new();
    }
}
