<?php

namespace Modules\Slider\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Slider\Database\Factories\SliderFactory;

class Slider extends Model
{
    /** @use HasFactory<SliderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['title', 'subtitle', 'image', 'extra_fields'];

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    protected static function newFactory(): SliderFactory
    {
        return SliderFactory::new();
    }
}
