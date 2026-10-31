<?php

namespace Modules\Blog\Models;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogContentTranslation extends Model
{
    protected $fillable = ['blog_content_id', 'lang_id', 'title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'extra_fields'];

    protected function casts(): array
    {
        return ['extra_fields' => 'array'];
    }

    public function blogContent(): BelongsTo
    {
        return $this->belongsTo(BlogContent::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang_id');
    }
}
