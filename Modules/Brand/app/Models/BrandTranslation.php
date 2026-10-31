<?php

namespace Modules\Brand\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandTranslation extends Model
{
    protected $fillable = ['brand_id', 'lang_id', 'name', 'short_description', 'extra_fields'];

    protected function casts(): array
    {
        return ['lang_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
