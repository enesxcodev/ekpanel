<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Projects\Database\Factories\ProjectCategoryFactory;

class ProjectCategory extends Model
{
    use HasFactory;

    protected $fillable = ['icon'];

    protected function casts(): array
    {
        return [];
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderBy('sort_order')->orderBy('id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectCategoryTranslation::class);
    }

    public function selectedTranslation(): HasOne
    {
        return $this->hasOne(ProjectCategoryTranslation::class)->where('lang_id', request()->attributes->get('projects_language_id'));
    }

    protected static function newFactory(): ProjectCategoryFactory
    {
        return ProjectCategoryFactory::new();
    }
}
