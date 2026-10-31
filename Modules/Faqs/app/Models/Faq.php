<?php

namespace Modules\Faqs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Faqs\Database\Factories\FaqFactory;

class Faq extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['category_id'];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'content', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if ($this->relationLoaded('translations')) {
                return $this->getRelation('translations')->first()?->getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FaqTranslation::class);
    }

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'category_id');
    }

    protected static function newFactory(): FaqFactory
    {
        return FaqFactory::new();
    }
}
