<?php

namespace App\Http\Middleware;

use App\Services\Analytics\VisitAnalytics;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackPublicPageView
{
    public function __construct(private VisitAnalytics $analytics) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        $path = $this->normalizePath($request->path());
        $ipAddress = $request->ip();

        if ($path === null || $ipAddress === null) {
            return $response;
        }

        try {
            $this->analytics->record(
                $ipAddress,
                $path,
                $request->user() ? 'user' : 'guest',
                $this->deviceType((string) $request->userAgent()),
            );
        } catch (Throwable $exception) {
            Log::warning('Public page view tracking failed.', ['exception' => $exception::class]);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        $userAgent = (string) $request->userAgent();
        $adminPrefix = trim((string) config('app.route_files.admin_prefix'), '/');
        $contentType = strtolower((string) $response->headers->get('Content-Type'));

        return $request->isMethod('GET')
            && (string) config('app.key') !== ''
            && $request->route() !== null
            && $response->getStatusCode() === Response::HTTP_OK
            && str_starts_with($contentType, 'text/html')
            && ! ($adminPrefix !== '' && $request->is($adminPrefix, $adminPrefix.'/*'))
            && ! $request->is('api', 'api/*')
            && ! preg_match('/(bot\b|crawler|spider|slurp|headless|lighthouse|preview|monitoring)/i', $userAgent);
    }

    private function normalizePath(string $path): ?string
    {
        $path = '/'.trim(preg_replace('#/+#', '/', rawurldecode($path)) ?? '', '/');

        if (strlen($path) > 255 || preg_match('/[\x00-\x1F\\\\]/', $path) || str_contains($path, '/..')) {
            return null;
        }

        return $path;
    }

    private function deviceType(string $userAgent): string
    {
        if (preg_match('/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i', $userAgent)) {
            return 'other';
        }

        if (preg_match('/(mobile|iphone|ipod|android.*mobile|windows phone)/i', $userAgent)) {
            return 'phone';
        }

        return $userAgent === '' ? 'other' : 'computer';
    }
}
