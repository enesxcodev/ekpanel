<?php

namespace Modules\Careers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = ['job_posting_id', 'candidate_name', 'email', 'phone', 'cover_letter', 'status'];

    public function posting(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }
}
