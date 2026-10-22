<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Events\Database\Factories\EventFactory;

class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'venue', 'starts_at', 'ends_at', 'capacity', 'description', 'is_active'];

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
}
