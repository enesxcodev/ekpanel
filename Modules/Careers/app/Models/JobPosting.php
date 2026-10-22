<?php

namespace Modules\Careers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Careers\Database\Factories\JobPostingFactory;

class JobPosting extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'location', 'employment_type', 'application_deadline', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['application_deadline' => 'date', 'is_active' => 'boolean'];
    }

    protected static function newFactory(): JobPostingFactory
    {
        return JobPostingFactory::new();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
