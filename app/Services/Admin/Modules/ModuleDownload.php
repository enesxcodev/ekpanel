<?php

namespace App\Services\Admin\Modules;

use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ModuleDownload
{
    public function assertAllowed(string $url): void
    {
        $parts = parse_url($url);
        if ($parts === false || ($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || ($parts['port'] ?? 443) !== 443
            || ! in_array(strtolower($parts['host'] ?? ''), config('module-distribution.allowed_hosts'), true)) {
            throw new RuntimeException('İndirme adresi izin verilen HTTPS kaynaklarından biri olmalıdır.');
        }
    }

    public function get(string $url, int $limit): string
    {
        for ($redirects = 0; $redirects < 5; $redirects++) {
            $this->assertAllowed($url);
            $response = Http::connectTimeout(10)->timeout(60)->withOptions([
                'allow_redirects' => false,
                'on_headers' => function (ResponseInterface $response) use ($limit): void {
                    if ((int) $response->getHeaderLine('Content-Length') > $limit) {
                        throw new RuntimeException('İndirme boyutu sınırı aşıldı.');
                    }
                },
                'progress' => function (int $total, int $downloaded) use ($limit): void {
                    if ($total > $limit || $downloaded > $limit) {
                        throw new RuntimeException('İndirme boyutu sınırı aşıldı.');
                    }
                },
            ])->get($url);
            if ($response->redirect()) {
                $url = $response->header('Location');

                continue;
            }
            if (! $response->successful()) {
                throw new RuntimeException('Modül kaynağına erişilemedi (HTTP '.$response->status().').');
            }
            $body = $response->body();
            if (strlen($body) > $limit) {
                throw new RuntimeException('İndirme boyutu sınırı aşıldı.');
            }

            return $body;
        }
        throw new RuntimeException('Çok fazla indirme yönlendirmesi.');
    }
}
