<?php

namespace Modules\Products\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
    protected $fillable = ['lang_id', 'title', 'slug', 'short_description', 'content', 'extra_fields'];

    protected function casts(): array
    {
        return ['lang_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
