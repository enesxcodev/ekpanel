<?php

namespace Modules\Store\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreProductTranslation extends Model
{
    protected $fillable = ['store_product_id', 'lang_id', 'title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'delivery_description', 'extra_fields'];

    protected function casts(): array
    {
        return ['lang_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(StoreProduct::class, 'store_product_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
