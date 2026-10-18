<?php

namespace App\Services\Media;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaFolder;
use App\Models\Media\MediaUsage;
use App\Models\Settings\SettingValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class MediaService
{
    public function __construct(private MediaFileInspector $inspector) {}

    /** @return array<string, mixed> */
    public function configuration(Admin $admin): array
    {
        return [
            'endpoints' => [
                'browse' => route('admin.media.data'), 'upload' => route('admin.media.store'),
                'folders' => route('admin.media.folders.store'), 'sync' => route('admin.media.sync'),
                'file' => route('admin.media.update', ['mediaFile' => '__ID__']),
                'folder' => route('admin.media.folders.update', ['folder' => '__ID__']),
                'restore' => route('admin.media.restore', ['mediaFile' => '__ID__']),
                'favorite' => route('admin.media.favorite', ['mediaFile' => '__ID__']),
            ],
            'permissions' => collect(['create', 'update', 'delete'])->mapWithKeys(fn (string $action): array => [$action => $admin->can('media.'.$action)])->all(),
            'extensions' => MediaFileInspector::EXTENSIONS,
            'maxBytes' => min(50 * 1024 * 1024, $this->iniBytes('upload_max_filesize'), $this->iniBytes('post_max_size')),
        ];
    }

    private function iniBytes(string $key): int
    {
        $value = trim((string) ini_get($key));
        if ($value === '' || (int) $value <= 0) {
            return 50 * 1024 * 1024;
        }

        return (int) ((float) $value * match (strtolower(substr($value, -1))) {
            'g' => 1073741824, 'm' => 1048576, 'k' => 1024, default => 1
        });
    }

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    public function browse(array $filters, Admin $admin): array
    {
        $view = $filters['view'] ?? 'all';
        $folderId = $filters['folder_id'] ?? null;
        $query = MediaFile::query()->with(['settingValues.definition', 'usages'])->withExists(['favoritedBy as is_favorite' => fn (Builder $query): Builder => $query->where('admins.id', $admin->id)]);
        if ($view === 'trash') {
            $query->onlyTrashed();
        } elseif ($view === 'favorites') {
            $query->whereHas('favoritedBy', fn (Builder $query): Builder => $query->where('admins.id', $admin->id));
        } elseif ($view === 'all') {
            $query->where('folder_id', $folderId);
        }
        if (! empty($filters['kind'])) {
            $query->where('kind', $filters['kind']);
        }
        if (! empty($filters['extensions'])) {
            $query->whereIn('extension', $filters['extensions']);
        }
        if (! empty($filters['search'])) {
            $query->whereRaw("name LIKE ? ESCAPE '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']).'%']);
        }
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $page = $query->orderBy($sort, $direction)->orderBy('id', $direction)->paginate(24);
        $folders = MediaFolder::query()->orderBy('name')->get(['id', 'parent_id', 'name'])->toArray();

        return [
            'files' => $page->getCollection()->map(fn (MediaFile $file): array => $this->fileData($file))->values(),
            'folders' => $folders,
            'pagination' => ['page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ];
    }

    /** @return array<string, mixed> */
    public function fileData(MediaFile $file): array
    {
        $file->loadMissing(['settingValues.definition', 'usages']);

        return [
            'id' => $file->id, 'folder_id' => $file->folder_id, 'name' => $file->name,
            'path' => $file->path, 'extension' => strtoupper($file->extension), 'kind' => $file->kind,
            'mime_type' => $file->mime_type, 'size' => $file->size, 'width' => $file->width, 'height' => $file->height,
            'alt_text' => $file->alt_text ?? '', 'created_at' => $file->created_at->toIso8601String(),
            'url' => Storage::disk($file->disk)->url($file->path),
            'download_url' => route('admin.media.download', $file),
            'is_favorite' => (bool) ($file->is_favorite ?? false), 'trashed' => $file->trashed(),
            'usages' => $file->settingValues->filter(fn (SettingValue $value): bool => $value->definition !== null)
                ->map(fn (SettingValue $value): array => [
                    'label' => 'Ayarlar / '.$value->definition->label,
                    'url' => route('admin.settings.index', ['section' => $value->definition->section_id]),
                ])->concat($file->usages->map(fn (MediaUsage $usage): array => [
                    'label' => $usage->label,
                    'url' => $usage->route_name && Route::has($usage->route_name) ? route($usage->route_name, $usage->owner_id) : null,
                ]))->values()->all(),
        ];
    }

    public function trash(MediaFile $file): void
    {
        DB::transaction(function () use ($file): void {
            $locked = MediaFile::query()->lockForUpdate()->findOrFail($file->id);
            if ($locked->settingValues()->exists() || $locked->usages()->exists()) {
                throw ValidationException::withMessages(['file' => 'Bu dosya içerikte kullanılıyor. Önce ilgili kayıttaki medya seçimini değiştirin veya kaldırın.']);
            }
            $locked->delete();
        });
    }

    public function attachUsage(MediaFile $media, Model $owner, string $field, string $label, ?string $routeName = null): void
    {
        MediaUsage::query()->updateOrCreate([
            'owner_type' => $owner->getMorphClass(), 'owner_id' => $owner->getKey(), 'field' => $field,
        ], ['media_id' => $media->id, 'label' => $label, 'route_name' => $routeName]);
    }

    public function detachUsages(Model $owner): void
    {
        MediaUsage::query()->where('owner_type', $owner->getMorphClass())->where('owner_id', $owner->getKey())->delete();
    }

    public function upload(UploadedFile $upload, ?int $folderId, Admin $admin): MediaFile
    {
        $metadata = $this->inspector->inspect($upload->getRealPath(), $upload->getClientOriginalName());
        $extension = $metadata['extension'] === 'jpeg' ? 'jpg' : $metadata['extension'];
        $path = '_media/'.date('Y/m').'/'.Str::uuid().'.'.$extension;
        $disk = Storage::disk('public');
        try {
            if ($metadata['kind'] === 'image') {
                $image = match ($extension) {
                    'jpg' => imagecreatefromjpeg($upload->getRealPath()),
                    'png' => imagecreatefrompng($upload->getRealPath()),
                    'webp' => imagecreatefromwebp($upload->getRealPath()),
                };
                $stream = fopen('php://temp/maxmemory:5242880', 'w+b');
                try {
                    imagealphablending($image, false);
                    imagesavealpha($image, true);
                    $written = match ($extension) {
                        'jpg' => imagejpeg($image, $stream, 90),
                        'png' => imagepng($image, $stream, 6),
                        'webp' => imagewebp($image, $stream, 90),
                    };
                    if (! $written) {
                        throw new RuntimeException('Görsel kodlanamadı.');
                    }
                    rewind($stream);
                    if (! $disk->put($path, $stream)) {
                        throw new RuntimeException('Dosya kaydedilemedi.');
                    }
                } finally {
                    fclose($stream);
                    imagedestroy($image);
                }
            } else {
                $stream = fopen($upload->getRealPath(), 'rb');
                try {
                    if (! $disk->put($path, $stream)) {
                        throw new RuntimeException('Dosya kaydedilemedi.');
                    }
                } finally {
                    fclose($stream);
                }
            }
            $metadata['size'] = $disk->size($path);
            $metadata['extension'] = $extension;

            return DB::transaction(fn (): MediaFile => MediaFile::query()->create([
                ...$metadata, 'folder_id' => $folderId, 'uploaded_by' => $admin->id,
                'disk' => 'public', 'path' => $path, 'name' => $upload->getClientOriginalName(),
            ]));
        } catch (Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        }
    }

    /** @param array<string, mixed> $data */
    public function updateFile(MediaFile $file, array $data): MediaFile
    {
        if (isset($data['name'])) {
            $name = trim($data['name']);
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== strtolower(pathinfo($file->name, PATHINFO_EXTENSION))) {
                throw ValidationException::withMessages(['name' => 'Yeniden adlandırırken dosya uzantısını koruyun.']);
            }
            $data['name'] = $name;
        }
        $file->update($data);

        return $file;
    }

    /** @param array{name: string, parent_id?: ?int} $data */
    public function saveFolder(array $data, ?MediaFolder $folder = null): MediaFolder
    {
        return Cache::lock('media-folder-tree', 15)->block(5, function () use ($data, $folder): MediaFolder {
            $parentId = $data['parent_id'] ?? null;
            $name = trim($data['name']);
            if ($name === '' || str_starts_with($name, '.')) {
                throw ValidationException::withMessages(['name' => 'Geçerli bir klasör adı girin.']);
            }
            $parent = $parentId;
            $visited = [];
            while ($parent !== null) {
                if ($folder?->id === (int) $parent || in_array($parent, $visited, true)) {
                    throw ValidationException::withMessages(['parent_id' => 'Klasör kendi içine veya alt klasörüne taşınamaz.']);
                }
                $visited[] = $parent;
                $parent = MediaFolder::query()->findOrFail($parent)->parent_id;
            }
            $duplicate = MediaFolder::query()->where('parent_id', $parentId)->where('name', $name);
            if ($folder?->exists) {
                $duplicate->whereKeyNot($folder->id);
            }
            if ($duplicate->exists()) {
                throw ValidationException::withMessages(['name' => 'Bu konumda aynı adlı bir klasör bulunuyor.']);
            }
            $folder ??= new MediaFolder;
            $folder->fill(['name' => $name, 'parent_id' => $parentId])->save();

            return $folder;
        });
    }

    public function deleteFolder(MediaFolder $folder): void
    {
        DB::transaction(function () use ($folder): void {
            if ($folder->children()->exists() || $folder->files()->withTrashed()->exists()) {
                throw ValidationException::withMessages(['folder' => 'Yalnızca boş klasörler silinebilir. Önce dosyaları ve alt klasörleri taşıyın.']);
            }
            if ($folder->source_path !== null) {
                $disk = Storage::disk('public');
                if ($disk->allFiles($folder->source_path) !== []) {
                    throw ValidationException::withMessages(['folder' => 'Bu klasör diskte dosya içeriyor ve silinemez.']);
                }
                $disk->deleteDirectory($folder->source_path);
            }
            $folder->delete();
        });
    }

    public function favorite(MediaFile $file, Admin $admin, bool $favorite): void
    {
        if ($favorite) {
            $file->favoritedBy()->syncWithoutDetaching([$admin->id]);
        } else {
            $file->favoritedBy()->detach($admin->id);
        }
    }

    /** @return array{added: int, skipped: int} */
    public function synchronize(): array
    {
        return Cache::lock('media-sync', 120)->block(5, function (): array {
            $disk = Storage::disk('public');
            $added = $skipped = 0;
            $folderIds = [];
            $directories = $disk->allDirectories();
            usort($directories, fn (string $a, string $b): int => substr_count($a, '/') <=> substr_count($b, '/'));
            foreach ($directories as $directory) {
                if (! $this->isImportablePath($directory)) {
                    continue;
                }
                $parentPath = dirname($directory);
                $folder = MediaFolder::query()->firstOrCreate(['source_path' => $directory], [
                    'name' => basename($directory), 'parent_id' => $folderIds[$parentPath] ?? null,
                ]);
                $folderIds[$directory] = $folder->id;
            }
            $known = MediaFile::withTrashed()->pluck('id', 'path')->all();
            foreach ($disk->allFiles() as $path) {
                if (! $this->isImportablePath($path) || isset($known[$path])) {
                    continue;
                }
                if (! in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), MediaFileInspector::EXTENSIONS, true)) {
                    $skipped++;

                    continue;
                }
                try {
                    $metadata = $this->inspector->inspect($disk->path($path), basename($path));
                } catch (ValidationException) {
                    $skipped++;

                    continue;
                }
                $file = MediaFile::query()->firstOrCreate(['path' => $path], [
                    ...$metadata, 'disk' => 'public', 'folder_id' => $folderIds[dirname($path)] ?? null, 'name' => basename($path),
                ]);
                if ($file->wasRecentlyCreated) {
                    $file->timestamps = false;
                    $file->created_at = $file->updated_at = date('Y-m-d H:i:s', $disk->lastModified($path));
                    $file->save();
                    $added++;
                }
            }

            return compact('added', 'skipped');
        });
    }

    private function isImportablePath(string $path): bool
    {
        foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
            if (str_starts_with($part, '.') || str_starts_with($part, '_')) {
                return false;
            }
        }
        $disk = Storage::disk('public');
        $root = realpath($disk->path(''));
        $resolved = realpath($disk->path($path));

        return $root !== false && $resolved !== false
            && str_starts_with(str_replace('\\', '/', $resolved), rtrim(str_replace('\\', '/', $root), '/').'/');
    }
}
