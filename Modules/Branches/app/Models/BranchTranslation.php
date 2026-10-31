<?php

namespace Modules\Branches\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchTranslation extends Model
{
    protected $fillable = ['lang_id', 'name', 'address_detail', 'phone', 'email', 'website', 'working_hours', 'map_url'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
