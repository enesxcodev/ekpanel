<?php

namespace Modules\Menus\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Menus\Database\Factories\MenuTranslationFactory;

class MenuTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['menu_id', 'lang_id', 'name'];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }

    protected static function newFactory(): MenuTranslationFactory
    {
        return MenuTranslationFactory::new();
    }
}
