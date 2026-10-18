<?php

namespace Modules\Galeriler\Services;

class VideoLink
{
    /** @return array{provider: string, id: string, embed_url: string, thumbnail_url: ?string}|null */
    public static function parse(?string $url): ?array
    {
        $parts = parse_url((string) $url);
        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port'])) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        $path = trim($parts['path'] ?? '', '/');
        $id = null;
        if ($host === 'youtu.be' && preg_match('/^[a-zA-Z0-9_-]{11}$/', $path)) {
            $id = $path;
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str($parts['query'] ?? '', $query);
                $id = is_string($query['v'] ?? null) ? $query['v'] : null;
            } elseif (preg_match('~^(?:embed|shorts|live)/([a-zA-Z0-9_-]{11})$~', $path, $match)) {
                $id = $match[1];
            }
        }
        if ($id !== null && preg_match('/^[a-zA-Z0-9_-]{11}$/', $id)) {
            return ['provider' => 'youtube', 'id' => $id, 'embed_url' => 'https://www.youtube-nocookie.com/embed/'.$id, 'thumbnail_url' => 'https://i.ytimg.com/vi/'.$id.'/hqdefault.jpg'];
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true) && preg_match('~^(?:video/)?([0-9]{1,15})(?:/([a-zA-Z0-9]+))?$~', $path, $match)) {
            parse_str($parts['query'] ?? '', $query);
            $hash = $match[2] ?? ($query['h'] ?? null);
            $embed = 'https://player.vimeo.com/video/'.$match[1];
            if (is_string($hash) && preg_match('/^[a-zA-Z0-9]+$/', $hash)) {
                $embed .= '?h='.$hash;
            }

            return ['provider' => 'vimeo', 'id' => $match[1], 'embed_url' => $embed, 'thumbnail_url' => null];
        }

        return null;
    }
}
