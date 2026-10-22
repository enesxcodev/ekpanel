<?php

namespace Modules\LogViewer\Services;

class LogFileBrowserService
{
    private const int MAX_TAIL_BYTES = 1_048_576;

    private const int CHUNK_BYTES = 8192;

    public function __construct(private readonly ?string $logsDirectory = null) {}

    /** @return array<int, array{type: string, name: string, path: string, size?: int, modified_at?: int, children?: array<int, array<string, mixed>>}> */
    public function tree(): array
    {
        $root = $this->rootPath();

        return $root === null ? [] : $this->scanDirectory($root, $root, '');
    }

    /** @return array<int, array{path: string, name: string, size: int, modified_at: int}> */
    public function files(): array
    {
        $files = [];
        $this->collectFiles($this->tree(), $files);

        return $files;
    }

    /** @return array{path: string, name: string, size: int, modified_at: int, lines: array<int, string>, truncated: bool}|null */
    public function readTail(string $relativePath, int $lineCount): ?array
    {
        if (! in_array($lineCount, [100, 200, 500], true)) {
            return null;
        }

        $filePath = $this->resolveLogPath($relativePath);
        if ($filePath === null || ! is_readable($filePath)) {
            return null;
        }

        clearstatcache(true, $filePath);
        $handle = @fopen($filePath, 'rb');
        if (! is_resource($handle)) {
            return null;
        }

        try {
            $stat = fstat($handle);
            if (! is_array($stat) || ! isset($stat['size'], $stat['mtime'])) {
                return null;
            }

            $fileSize = (int) $stat['size'];
            $position = $fileSize;
            $bytesRead = 0;
            $newlineCount = 0;
            $chunks = [];

            while ($position > 0 && $bytesRead < self::MAX_TAIL_BYTES && $newlineCount < $lineCount) {
                $chunkSize = min(self::CHUNK_BYTES, $position, self::MAX_TAIL_BYTES - $bytesRead);
                $position -= $chunkSize;
                if (fseek($handle, $position) !== 0) {
                    return null;
                }

                $chunk = fread($handle, $chunkSize);
                if (! is_string($chunk) || $chunk === '') {
                    break;
                }

                $chunks[] = $chunk;
                $bytesRead += strlen($chunk);
                $newlineCount += substr_count($chunk, "\n");
            }

            $buffer = implode('', array_reverse($chunks));
            $parts = $buffer === '' ? [] : explode("\n", $buffer);
            if ($buffer !== '' && str_ends_with($buffer, "\n")) {
                array_pop($parts);
            }

            $partialFirstLine = false;
            if ($position > 0) {
                if (fseek($handle, $position - 1) === 0) {
                    $partialFirstLine = fread($handle, 1) !== "\n";
                }
                if ($partialFirstLine && count($parts) > 1) {
                    array_shift($parts);
                } elseif ($partialFirstLine && isset($parts[0])) {
                    $parts[0] = '… '.substr($parts[0], 0, self::MAX_TAIL_BYTES - strlen('… '));
                }
            }

            $lines = array_slice($parts, -$lineCount);
            clearstatcache(true, $filePath);
            $currentStat = @stat($filePath);
            if (! is_array($currentStat) || ! $this->isContainedFile($filePath)) {
                return null;
            }

            return [
                'path' => $relativePath,
                'name' => basename($relativePath),
                'size' => (int) ($currentStat['size'] ?? $fileSize),
                'modified_at' => (int) ($currentStat['mtime'] ?? $stat['mtime']),
                'lines' => array_values($lines),
                'truncated' => $position > 0,
            ];
        } finally {
            fclose($handle);
        }
    }

    public function isSafeLogPath(string $relativePath): bool
    {
        if ($relativePath === '' || str_contains($relativePath, "\0") || str_contains($relativePath, '\\') || str_starts_with($relativePath, '/')) {
            return false;
        }

        $segments = explode('/', $relativePath);
        foreach ($segments as $index => $segment) {
            if ($index < count($segments) - 1) {
                if (! $this->isSafeSegment($segment)) {
                    return false;
                }

                continue;
            }

            if (! $this->isSafeSegment($segment) || ! $this->isLogFilename($segment)) {
                return false;
            }
        }

        return $this->rootPath() !== null;
    }

    public function isAvailable(): bool
    {
        return $this->rootPath() !== null;
    }

    private function rootPath(): ?string
    {
        $root = realpath($this->logsDirectory ?? storage_path('logs'));

        return $root !== false && is_dir($root) && is_readable($root) ? $root : null;
    }

    /** @return array<int, array{type: string, name: string, path: string, size?: int, modified_at?: int, children?: array<int, array<string, mixed>>}> */
    private function scanDirectory(string $root, string $directory, string $relativePath): array
    {
        $entries = @scandir($directory);
        if (! is_array($entries)) {
            return [];
        }

        $nodes = [];
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || ! $this->isSafeSegment($entry)) {
                continue;
            }

            $absolutePath = $directory.DIRECTORY_SEPARATOR.$entry;
            if (is_link($absolutePath)) {
                continue;
            }

            $realPath = realpath($absolutePath);
            if ($realPath === false || ! $this->isWithinRoot($root, $realPath)) {
                continue;
            }

            $path = $relativePath === '' ? $entry : $relativePath.'/'.$entry;
            if (is_dir($realPath)) {
                $children = $this->scanDirectory($root, $realPath, $path);
                if ($children !== []) {
                    $nodes[] = ['type' => 'directory', 'name' => $entry, 'path' => $path, 'children' => $children];
                }

                continue;
            }

            if (! is_file($realPath) || ! $this->isLogFilename($entry) || ! is_readable($realPath)) {
                continue;
            }

            $stat = @stat($realPath);
            if (! is_array($stat)) {
                continue;
            }
            $nodes[] = [
                'type' => 'file', 'name' => $entry, 'path' => $path,
                'size' => (int) ($stat['size'] ?? 0), 'modified_at' => (int) ($stat['mtime'] ?? 0),
            ];
        }

        usort($nodes, static function (array $left, array $right): int {
            if ($left['type'] !== $right['type']) {
                return $left['type'] === 'directory' ? -1 : 1;
            }

            return strnatcasecmp($left['name'], $right['name']);
        });

        return $nodes;
    }

    /** @param array<int, array<string, mixed>> $nodes @param array<int, array{path: string, name: string, size: int, modified_at: int}> $files */
    private function collectFiles(array $nodes, array &$files): void
    {
        foreach ($nodes as $node) {
            if ($node['type'] === 'directory') {
                $this->collectFiles($node['children'] ?? [], $files);

                continue;
            }
            $files[] = [
                'path' => (string) $node['path'], 'name' => (string) $node['name'],
                'size' => (int) $node['size'], 'modified_at' => (int) $node['modified_at'],
            ];
        }
    }

    private function resolveLogPath(string $relativePath): ?string
    {
        if ($relativePath === '' || str_contains($relativePath, "\0") || str_contains($relativePath, '\\') || str_starts_with($relativePath, '/')) {
            return null;
        }

        $segments = explode('/', $relativePath);
        foreach ($segments as $index => $segment) {
            if ($index < count($segments) - 1) {
                if (! $this->isSafeSegment($segment)) {
                    return null;
                }

                continue;
            }
            if (! $this->isSafeSegment($segment) || ! $this->isLogFilename($segment)) {
                return null;
            }
        }

        $root = $this->rootPath();
        if ($root === null) {
            return null;
        }

        $candidate = $root.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $segments);
        $cursor = $root;
        foreach ($segments as $segment) {
            $cursor .= DIRECTORY_SEPARATOR.$segment;
            if (is_link($cursor)) {
                return null;
            }
        }

        $realPath = realpath($candidate);
        if ($realPath === false || ! is_file($realPath) || ! is_readable($realPath) || ! $this->isWithinRoot($root, $realPath)) {
            return null;
        }

        return $realPath;
    }

    private function isSafeSegment(string $segment): bool
    {
        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\z/D', $segment) === 1;
    }

    private function isLogFilename(string $filename): bool
    {
        return preg_match('/\A[A-Za-z0-9][A-Za-z0-9._-]*\.log\z/iD', $filename) === 1;
    }

    private function isContainedFile(string $path): bool
    {
        $root = $this->rootPath();

        return $root !== null && $this->isWithinRoot($root, $path) && ! is_link($path);
    }

    private function isWithinRoot(string $root, string $path): bool
    {
        $normalizedRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (DIRECTORY_SEPARATOR === '\\') {
            $normalizedRoot = strtolower($normalizedRoot);
            $normalizedPath = strtolower($normalizedPath);
        }

        return str_starts_with($normalizedPath, $normalizedRoot);
    }
}
