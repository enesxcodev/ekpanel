<?php

namespace App\Models\Forms;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['form_name', 'name', 'email', 'phone', 'status', 'answers', 'related_content'];

    protected function casts(): array
    {
        return ['answers' => 'array', 'related_content' => 'array'];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(SiteForm::class, 'site_form_id');
    }
}
