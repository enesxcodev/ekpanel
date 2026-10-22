<?php

namespace Modules\Konum\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class KonumSource
{
    public const TABLES = ['cities', 'towns', 'districts', 'neighbourhoods', 'streets'];

    /** @return array<string, string> */
    public function prepare(?string $directory = null): array
    {
        $directory ??= storage_path('app/private/konum-source/'.config('konum.source.commit'));
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Kaynak klasörü oluşturulamadı.');
        }
        $paths = [];
        foreach (self::TABLES as $table) {
            $path = $directory.DIRECTORY_SEPARATOR.$table.'.sql';
            $metadata = config('konum.source.files.'.$table);
            if (! is_array($metadata)) {
                throw new RuntimeException('Kaynak dosya metadata eksik.');
            }
            if (! is_file($path)) {
                $temporary = $path.'.'.bin2hex(random_bytes(6)).'.part';
                try {
                    Http::connectTimeout(15)->timeout(600)->withOptions([
                        'sink' => $temporary,
                        'allow_redirects' => false,
                        'progress' => static function (float $total, float $downloaded) use ($metadata): void {
                            if ($downloaded > $metadata['bytes']) {
                                throw new RuntimeException('Kaynak indirme boyutu aşıldı.');
                            }
                        },
                    ])->get('https://raw.githubusercontent.com/'.config('konum.source.repository').'/'.config('konum.source.commit').'/db/'.$table.'.sql')->throw();
                    $this->verify($temporary, $metadata);
                    if (! rename($temporary, $path)) {
                        throw new RuntimeException('Kaynak dosya kaydedilemedi.');
                    }
                } finally {
                    if (is_file($temporary)) {
                        unlink($temporary);
                    }
                }
            }
            $this->verify($path, $metadata);
            $paths[$table] = $path;
        }

        return $paths;
    }

    /** @param array{bytes: int, sha256: string} $metadata */
    private function verify(string $path, array $metadata): void
    {
        if (filesize($path) !== $metadata['bytes'] || ! hash_equals($metadata['sha256'], hash_file('sha256', $path))) {
            throw new RuntimeException('Kaynak boyutu veya SHA-256 uyuşmuyor: '.basename($path));
        }
    }
}
