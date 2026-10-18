<?php

namespace App\Http\Controllers;

use App\Http\Requests\Media\BrowseMediaRequest;
use App\Http\Requests\Media\SaveMediaFolderRequest;
use App\Http\Requests\Media\UpdateMediaFileRequest;
use App\Http\Requests\Media\UploadMediaRequest;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaFolder;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function index(Request $request): View
    {
        $this->authorize('media.view');

        return view('admin.media.index', ['mediaConfig' => $this->mediaService->configuration($request->user('admin'))]);
    }

    public function data(BrowseMediaRequest $request): JsonResponse
    {
        return response()->json($this->mediaService->browse($request->validated(), $request->user('admin')));
    }

    public function show(MediaFile $mediaFile): JsonResponse
    {
        $this->authorize('media.view');

        return response()->json(['file' => $this->mediaService->fileData($mediaFile)]);
    }

    public function store(UploadMediaRequest $request): JsonResponse
    {
        $file = $this->mediaService->upload($request->file('file'), $request->validated('folder_id'), $request->user('admin'));

        return response()->json(['file' => $this->mediaService->fileData($file), 'message' => 'Dosya yüklendi.'], 201);
    }

    public function update(UpdateMediaFileRequest $request, MediaFile $mediaFile): JsonResponse
    {
        $file = $this->mediaService->updateFile($mediaFile, $request->validated());

        return response()->json(['file' => $this->mediaService->fileData($file), 'message' => 'Dosya güncellendi.']);
    }

    public function destroy(MediaFile $mediaFile): JsonResponse
    {
        $this->authorize('media.delete');
        $this->mediaService->trash($mediaFile);

        return response()->json(['message' => 'Dosya çöp kutusuna taşındı.']);
    }

    public function restore(MediaFile $mediaFile): JsonResponse
    {
        $this->authorize('media.delete');
        $mediaFile->restore();

        return response()->json(['message' => 'Dosya geri yüklendi.']);
    }

    public function favorite(Request $request, MediaFile $mediaFile): JsonResponse
    {
        $this->authorize('media.view');
        $data = $request->validate(['favorite' => ['required', 'boolean']]);
        $this->mediaService->favorite($mediaFile, $request->user('admin'), $data['favorite']);

        return response()->json(['message' => $data['favorite'] ? 'Favorilere eklendi.' : 'Favorilerden kaldırıldı.']);
    }

    public function storeFolder(SaveMediaFolderRequest $request): JsonResponse
    {
        $folder = $this->mediaService->saveFolder($request->validated());

        return response()->json(['folder' => $folder, 'message' => 'Klasör oluşturuldu.'], 201);
    }

    public function updateFolder(SaveMediaFolderRequest $request, MediaFolder $folder): JsonResponse
    {
        $folder = $this->mediaService->saveFolder($request->validated(), $folder);

        return response()->json(['folder' => $folder, 'message' => 'Klasör güncellendi.']);
    }

    public function destroyFolder(MediaFolder $folder): JsonResponse
    {
        $this->authorize('media.delete');
        $this->mediaService->deleteFolder($folder);

        return response()->json(['message' => 'Boş klasör silindi.']);
    }

    public function synchronize(): JsonResponse
    {
        $this->authorize('media.create');
        $result = $this->mediaService->synchronize();

        return response()->json([...$result, 'message' => $result['added'].' dosya eklendi; '.$result['skipped'].' desteklenmeyen veya geçersiz dosya atlandı.']);
    }

    public function download(MediaFile $mediaFile): StreamedResponse
    {
        $this->authorize('media.view');

        return Storage::disk($mediaFile->disk)->download($mediaFile->path, $mediaFile->name, [
            'Content-Type' => $mediaFile->mime_type, 'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "sandbox; default-src 'none'",
        ]);
    }
}
