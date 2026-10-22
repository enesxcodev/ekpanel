<?php

namespace Modules\LogViewer\Http\Controllers;

use App\Enums\AdminType\AdminTypeEnum;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\LogViewer\Services\LogFileBrowserService;

class LogViewerController extends Controller
{
    public function __invoke(Request $request, LogFileBrowserService $logs): View
    {
        $this->authorize('logviewer.view');
        abort_unless($request->user('admin')?->type === AdminTypeEnum::SuperAdmin, 403);

        $query = $request->validate([
            'file' => ['nullable', 'string', 'max:512'],
            'lines' => ['nullable', 'string', Rule::in(['100', '200', '500'])],
        ]);
        $lineCount = (int) ($query['lines'] ?? 200);
        $files = $logs->files();
        $selectedPath = $query['file'] ?? null;

        if (is_string($selectedPath) && ! $logs->isSafeLogPath($selectedPath)) {
            abort(404);
        }

        if (! is_string($selectedPath) && $files !== []) {
            usort($files, static fn (array $left, array $right): int => $right['modified_at'] <=> $left['modified_at']);
            $selectedPath = $files[0]['path'];
        }

        $selectedLog = is_string($selectedPath) ? $logs->readTail($selectedPath, $lineCount) : null;
        $readError = is_string($selectedPath) && $selectedLog === null;
        if ($readError) {
            $selectedPath = null;
        }

        return view('logviewer::index', [
            'tree' => $logs->tree(),
            'selectedPath' => $selectedPath,
            'selectedLog' => $selectedLog,
            'lineCount' => $lineCount,
            'readError' => $readError,
            'logsAvailable' => $logs->isAvailable(),
            'lastReadAt' => now()->format('d.m.Y H:i:s'),
        ]);
    }
}
