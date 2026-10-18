<?php

namespace Modules\Galeriler\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Galeriler\Database\Factories\GalerilerItemFactory;
use Modules\Galeriler\Services\VideoLink;

class GalerilerItem extends Model
{
    use HasFactory;

    protected $fillable = ['galeriler_category_id', 'type', 'title', 'description', 'image', 'image_media_id', 'video_url', 'sort_order', 'extra_fields'];

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'galeriler_category_id' => 'integer', 'sort_order' => 'integer', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GalerilerCategory::class, 'galeriler_category_id');
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->imageMedia ? Storage::disk($this->imageMedia->disk)->url($this->imageMedia->path) : null);
    }

    protected function video(): Attribute
    {
        return Attribute::get(fn (): ?array => VideoLink::parse($this->video_url));
    }

    protected static function newFactory(): GalerilerItemFactory
    {
        return GalerilerItemFactory::new();
    }
}
