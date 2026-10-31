<?php

namespace Modules\Projects\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTranslation extends Model
{
    protected $fillable = ['project_id', 'lang_id', 'title', 'slug', 'content', 'extra_fields'];

    protected function casts(): array
    {
        return ['project_id' => 'integer', 'lang_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
