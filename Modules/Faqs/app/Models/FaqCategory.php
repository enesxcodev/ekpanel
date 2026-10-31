<?php

namespace Modules\Faqs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Faqs\Database\Factories\FaqCategoryFactory;

class FaqCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
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
        return $this->hasMany(FaqCategoryTranslation::class);
    }

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id');
    }

    protected static function newFactory(): FaqCategoryFactory
    {
        return FaqCategoryFactory::new();
    }
}
