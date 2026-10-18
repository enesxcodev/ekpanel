<?php

namespace App\Console\Commands;

use App\Models\Media\MediaFile;
use App\Services\Media\MediaFileInspector;
use App\Services\Media\MediaService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LinkModuleMedia extends Command
{
    protected $signature = 'media:link-content {model} {field=image} {--label=} {--title=title} {--route=}';

    protected $description = 'Mevcut içerik dosya yollarını dosyaları değiştirmeden medya kayıtlarıyla ilişkilendirir';

    public function handle(MediaService $mediaService, MediaFileInspector $inspector): int
    {
        $class = $this->argument('model');
        $field = $this->argument('field');
        if (! is_subclass_of($class, Model::class) || ! in_array($field, ['image', 'file'], true)) {
            $this->error('Geçerli bir Eloquent model sınıfı ve image/file alanı belirtin.');

            return self::FAILURE;
        }
        $model = new $class;
        if (! Schema::hasColumn($model->getTable(), $field.'_media_id')) {
            $this->error('Önce modülün medya migration dosyasını çalıştırın.');

            return self::FAILURE;
        }
        $linked = $skipped = 0;
        $class::query()->whereNull($field.'_media_id')->whereNotNull($field)->where($field, '!=', '')->eachById(function (Model $owner) use ($field, $mediaService, $inspector, &$linked, &$skipped): void {
            DB::transaction(function () use ($owner, $field, $mediaService, $inspector, &$linked, &$skipped): void {
                $owner = $owner->newQuery()->lockForUpdate()->findOrFail($owner->getKey());
                if ($owner->getAttribute($field.'_media_id')) {
                    return;
                }
                $media = MediaFile::query()->where('disk', 'public')->where('path', $owner->getAttribute($field))->lockForUpdate()->first();
                if ($media === null || ! Storage::disk('public')->exists($media->path)) {
                    $skipped++;

                    return;
                }
                try {
                    $metadata = $inspector->inspect(Storage::disk('public')->path($media->path), basename($media->path));
                } catch (ValidationException) {
                    $skipped++;

                    return;
                }
                if ($field === 'image' && $metadata['kind'] !== 'image') {
                    $skipped++;

                    return;
                }
                $owner->setAttribute($field.'_media_id', $media->id)->saveQuietly();
                $title = $owner->getAttribute($this->option('title')) ?? $owner->getKey();
                $mediaService->attachUsage($media, $owner, $field, ($this->option('label') ?: class_basename($owner)).' / '.$title, $this->option('route'));
                $linked++;
            });
        });
        $this->info($linked.' kayıt ilişkilendirildi; '.$skipped.' kayıt mevcut yolu korunarak atlandı.');

        return self::SUCCESS;
    }
}
