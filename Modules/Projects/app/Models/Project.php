<?php

namespace Modules\Projects\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Modules\Projects\Database\Factories\ProjectFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['project_category_id', 'project_date', 'is_active', 'sort_order', 'cover_media_id'];

    protected function casts(): array
    {
        return ['project_date' => 'date', 'is_active' => 'boolean', 'sort_order' => 'integer', 'cover_media_id' => 'integer'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProjectTranslation::class);
    }

    public function selectedTranslation(): HasOne
    {
        return $this->hasOne(ProjectTranslation::class)->where('lang_id', request()->attributes->get('projects_language_id'));
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(MediaFile::class, 'project_images')->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_id');
    }

    protected function coverUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->coverMedia ? Storage::disk($this->coverMedia->disk)->url($this->coverMedia->path) : null);
    }

    #[Scope]
    protected function published(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }
}
