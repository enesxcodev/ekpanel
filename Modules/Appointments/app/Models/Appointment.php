<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Appointments\Database\Factories\AppointmentFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['customer_name', 'customer_email', 'customer_phone', 'service_name', 'starts_at', 'duration_minutes', 'status', 'notes'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'duration_minutes' => 'integer'];
    }

    protected static function newFactory(): AppointmentFactory
    {
        return AppointmentFactory::new();
    }
}
