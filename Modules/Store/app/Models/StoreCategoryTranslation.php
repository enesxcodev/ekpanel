<?php

namespace Modules\Store\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreCategoryTranslation extends Model
{
    protected $fillable = ['store_category_id', 'lang_id', 'name', 'slug', 'extra_fields'];

    protected function casts(): array
    {
        return ['lang_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(StoreCategory::class, 'store_category_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
