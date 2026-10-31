<?php

namespace Modules\Documents\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Documents\Database\Factories\DocumentCategoryFactory;

class DocumentCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['icon'];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentCategoryTranslation::class);
    }

    protected static function newFactory(): DocumentCategoryFactory
    {
        return DocumentCategoryFactory::new();
    }
}
