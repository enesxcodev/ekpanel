<?php

namespace Modules\Pages\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageCategoryTranslation extends Model
{
    protected $fillable = ['page_category_id', 'lang_id', 'scope_id', 'name', 'slug', 'short_description', 'extra_fields'];

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    public function pageCategory(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
