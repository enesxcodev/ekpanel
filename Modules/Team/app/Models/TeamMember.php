<?php

namespace Modules\Team\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Team\Database\Factories\TeamMemberFactory;

class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory;

    protected $with = ['translations.language'];

    protected $fillable = ['image', 'image_media_id', 'is_active'];

    public function translations(): HasMany
    {
        return $this->hasMany(TeamMemberTranslation::class);
    }

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['name', 'title', 'extra_fields'], true) && ! array_key_exists($key, $this->attributes)) {
            if ($this->relationLoaded('selectedTranslation')) {
                return $this->getRelation('selectedTranslation')?->getAttribute($key);
            }
            if ($this->relationLoaded('translations')) {
                $code = request()->attributes->get('team_language', 'tr');
                $translation = $this->getRelation('translations')->first(fn (TeamMemberTranslation $item): bool => $item->relationLoaded('language') && $item->language?->code === $code);

                return $translation?->getAttribute($key);
            }
        }

        return parent::getAttribute($key);
    }

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->imageMedia;
            $path = $media?->path ?? ($this->image_media_id ? null : $this->image);

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): TeamMemberFactory
    {
        return TeamMemberFactory::new();
    }
}
