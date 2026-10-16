<?php

namespace Modules\Services\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Services\Database\Factories\ServiceFactory;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['service_category_id', 'title', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'image', 'icon', 'is_active', 'extra_fields'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }
}
