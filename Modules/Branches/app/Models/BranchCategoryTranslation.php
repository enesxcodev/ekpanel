<?php

namespace Modules\Branches\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchCategoryTranslation extends Model
{
    protected $fillable = ['lang_id', 'name'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BranchCategory::class, 'branch_category_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
