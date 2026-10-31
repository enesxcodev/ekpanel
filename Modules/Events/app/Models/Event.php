<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Events\Database\Factories\EventFactory;
use Modules\Events\Services\EventLanguageService;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['starts_at', 'ends_at', 'capacity', 'is_active'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'capacity' => 'integer', 'is_active' => 'boolean'];
    }

    protected static function newFactory(): EventFactory
    {
        return EventFactory::new();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(EventTranslation::class);
    }

    public function selectedTranslation(): HasOne
    {
        $languageId = request()->attributes->get('events_language_id') ?? app(EventLanguageService::class)->forFrontend()?->id;

        return $this->hasOne(EventTranslation::class)->where('lang_id', $languageId);
    }

    public function getAttribute($key): mixed
    {
        if (in_array($key, ['title', 'venue', 'description'], true) && ! array_key_exists($key, $this->attributes)) {
            $translation = $this->relationLoaded('selectedTranslation')
                ? $this->getRelation('selectedTranslation')
                : $this->selectedTranslation()->first();
            if (! $this->relationLoaded('selectedTranslation')) {
                $this->setRelation('selectedTranslation', $translation);
            }

            return $translation?->getAttribute($key);
        }

        return parent::getAttribute($key);
    }
}
