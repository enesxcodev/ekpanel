<?php

namespace App\Services\Fields;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FieldFiles
{
    /** @var list<string> */
    private array $newFiles = [];

    /** @var list<string> */
    private array $oldFiles = [];

    public function replace(?UploadedFile $upload, ?string $previous, string $directory, bool $remove = false): ?string
    {
        if ($upload === null && ! $remove) {
            return $previous;
        }
        if ($previous !== null) {
            $this->oldFiles[] = $previous;
        }
        if ($upload === null) {
            return null;
        }
        $path = $upload->store($directory, 'public');
        if ($path === false) {
            throw new RuntimeException('Dosya kaydedilemedi.');
        }
        $this->newFiles[] = $path;

        return $path;
    }

    /** @template T @param Closure(self): T $persist @return T */
    public function transaction(Closure $persist): mixed
    {
        $this->newFiles = $this->oldFiles = [];
        try {
            $result = DB::transaction(fn (): mixed => $persist($this));
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($this->newFiles);
            throw $exception;
        }
        Storage::disk('public')->delete($this->oldFiles);

        return $result;
    }
}
