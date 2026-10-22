<?php

namespace Modules\Events\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'attendee_name', 'email', 'phone', 'status'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
