<?php

namespace Modules\Projects\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCategoryTranslation extends Model
{
    protected $fillable = ['project_category_id', 'lang_id', 'name', 'slug', 'extra_fields'];

    protected function casts(): array
    {
        return ['project_category_id' => 'integer', 'lang_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
