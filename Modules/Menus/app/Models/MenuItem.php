<?php

namespace Modules\Menus\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Menus\Database\Factories\MenuItemFactory;

class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    protected $fillable = ['parent_id', 'label', 'icon', 'target_type', 'url', 'route_name', 'route_parameters', 'anchor', 'source', 'source_id', 'dynamic_children', 'is_active', 'new_tab', 'sort_order'];

    protected function casts(): array
    {
        return ['route_parameters' => 'array', 'dynamic_children' => 'boolean', 'is_active' => 'boolean', 'new_tab' => 'boolean', 'sort_order' => 'integer'];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }

    protected static function newFactory(): MenuItemFactory
    {
        return MenuItemFactory::new();
    }
}
