<?php

namespace App\Services\Media;

use Illuminate\Validation\ValidationException;
use ZipArchive;

class MediaFileInspector
{
    public const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'pdf', 'doc', 'docx', 'pptx', 'txt'];

    /** @return array{extension: string, mime_type: string, kind: string, size: int, width: ?int, height: ?int} */
    public function inspect(string $path, string $name): array
    {
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (mb_strlen($name) > 255 || preg_match('/\p{C}/u', $name)
            || ! in_array($extension, self::EXTENSIONS, true)
            || preg_match('/[\x00-\x1f\x7f\\\\\/]/', $name)
            || preg_match('/(?:^|\.)(?:php\d*|phtml|phar|cgi|pl|exe|com|bat|cmd|sh|asp|aspx|jsp|js|html?|svg|shtml)(?:\.|$)/i', $name)) {
            $this->reject('Dosya adı veya uzantısı kabul edilmiyor.');
        }
        $size = filesize($path);
        if ($size === false || $size < 1 || $size > 50 * 1024 * 1024) {
            $this->reject('Dosya boş olamaz ve en fazla 50 MB olabilir.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);
        $allowed = [
            'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'],
            'pdf' => ['application/pdf'], 'txt' => ['text/plain'],
            'mp4' => ['video/mp4', 'application/mp4'], 'webm' => ['video/webm'],
            'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/zip'],
        ];
        if (! in_array($mime, $allowed[$extension], true)) {
            $this->reject('Dosyanın içeriği ile uzantısı uyuşmuyor.');
        }
        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true);
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->reject('Dosya okunamadı.');
        }
        $tail = '';
        while (! $isImage && ! feof($handle)) {
            $chunk = fread($handle, 65536);
            if ($chunk === false) {
                fclose($handle);
                $this->reject('Dosya okunamadı.');
            }
            if (preg_match('/<\?(?:php\b|=)|<script\b|#!\s*\/.*(?:sh|python|perl)/i', $tail.$chunk)) {
                fclose($handle);
                $this->reject('Çalıştırılabilir kod içeren dosyalar kabul edilmiyor.');
            }
            $tail = substr($chunk, -100);
        }
        fclose($handle);
        $width = $height = null;
        $kind = 'document';
        if ($isImage) {
            $dimensions = @getimagesize($path);
            if (! $dimensions || $dimensions[0] > 12000 || $dimensions[1] > 12000 || $dimensions[0] * $dimensions[1] > 24000000) {
                $this->reject('Görsel geçersiz veya 24 megapiksel sınırını aşıyor.');
            }
            $image = match ($extension) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($path), 'png' => @imagecreatefrompng($path), 'webp' => @imagecreatefromwebp($path),
            };
            if ($image === false) {
                $this->reject('Görselin içeriği çözümlenemedi.');
            }
            imagedestroy($image);
            $this->inspectImageMetadata($path, $extension);
            [$width, $height] = $dimensions;
            $kind = 'image';
        } elseif (in_array($extension, ['docx', 'pptx'], true)) {
            $this->inspectOfficeArchive($path, $extension);
            $mime = $allowed[$extension][0];
        } elseif ($extension === 'doc') {
            $bytes = file_get_contents($path);
            if (! str_starts_with($bytes, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1")
                || ! str_contains($bytes, "W\0o\0r\0d\0D\0o\0c\0u\0m\0e\0n\0t\0")
                || preg_match('/V\x00B\x00A\x00|M\x00a\x00c\x00r\x00o\x00|O\x00b\x00j\x00e\x00c\x00t\x00P\x00o\x00o\x00l/i', $bytes)) {
                $this->reject('Word belgesi geçersiz veya etkin içerik içeriyor. DOCX kullanın.');
            }
            $mime = 'application/msword';
        } elseif ($extension === 'pdf') {
            $bytes = file_get_contents($path);
            $decodedNames = preg_replace_callback('/#([0-9a-f]{2})/i', fn (array $match): string => chr(hexdec($match[1])), $bytes);
            if (! str_starts_with($bytes, '%PDF-') || ! str_contains(substr($bytes, -2048), '%%EOF')
                || preg_match('/\/(?:JavaScript|JS|Launch|EmbeddedFile|OpenAction|RichMedia|Encrypt|AA)\b/i', $decodedNames)) {
                $this->reject('PDF geçersiz, şifreli veya etkin içerik barındırıyor.');
            }
        } elseif ($extension === 'txt') {
            $bytes = file_get_contents($path);
            if (! mb_check_encoding($bytes, 'UTF-8') || str_contains($bytes, "\0") || preg_match('/<\s*(?:html|iframe|object|embed|svg)\b/i', $bytes)) {
                $this->reject('Yalnızca düz UTF-8 metin dosyaları kabul edilir.');
            }
        } else {
            $header = file_get_contents($path, false, null, 0, 4096);
            if (($extension === 'mp4' && (substr($header, 4, 4) !== 'ftyp' || ! preg_match('/(?:isom|iso[2-9]|mp4[12]|avc1|M4V )/', substr($header, 8, 64))))
                || ($extension === 'webm' && (! str_starts_with($header, "\x1A\x45\xDF\xA3") || ! str_contains($header, 'webm')))) {
                $this->reject('Video dosyasının biçimi doğrulanamadı.');
            }
            if ($extension === 'mp4') {
                $this->inspectMp4($path, $size);
            } else {
                $bytes = file_get_contents($path);
                foreach (["\x18\x53\x80\x67", "\x16\x54\xAE\x6B", "\x1F\x43\xB6\x75"] as $marker) {
                    if (! str_contains($bytes, $marker)) {
                        $this->reject('WebM video yapısı eksik veya bozuk.');
                    }
                }
                if (! preg_match('/V_(?:VP8|VP9|AV1)/', $bytes)) {
                    $this->reject('WebM dosyasında desteklenen bir video akışı bulunamadı.');
                }
            }
            $kind = 'video';
        }

        return ['extension' => $extension, 'mime_type' => $mime, 'kind' => $kind, 'size' => $size, 'width' => $width, 'height' => $height];
    }

    /** Compressed pixels can contain code-like bytes; scan metadata and trailing content instead. */
    private function inspectImageMetadata(string $path, string $extension): void
    {
        $bytes = file_get_contents($path);
        if ($bytes === false) {
            $this->reject('Dosya okunamadı.');
        }
        $size = strlen($bytes);
        if ($extension === 'png') {
            $offset = 8;
            while ($offset + 12 <= $size) {
                $length = unpack('Nlength', substr($bytes, $offset, 4))['length'];
                $type = substr($bytes, $offset + 4, 4);
                if ($length > $size - $offset - 12) {
                    $this->reject('Görsel dosyasının yapısı geçersiz.');
                }
                if (in_array($type, ['tEXt', 'iTXt', 'eXIf'], true)) {
                    $this->rejectExecutableContent(substr($bytes, $offset + 8, $length));
                }
                $offset += $length + 12;
                if ($type === 'IEND') {
                    $this->rejectExecutableContent(substr($bytes, $offset));

                    return;
                }
            }
        } elseif ($extension === 'webp') {
            $end = unpack('Vlength', substr($bytes, 4, 4))['length'] + 8;
            $offset = 12;
            while ($offset + 8 <= $end && $end <= $size) {
                $type = substr($bytes, $offset, 4);
                $length = unpack('Vlength', substr($bytes, $offset + 4, 4))['length'];
                if ($length > $end - $offset - 8) {
                    $this->reject('Görsel dosyasının yapısı geçersiz.');
                }
                if (in_array($type, ['EXIF', 'XMP '], true)) {
                    $this->rejectExecutableContent(substr($bytes, $offset + 8, $length));
                }
                $offset += 8 + $length + ($length % 2);
            }
            if ($offset === $end && $end <= $size) {
                $this->rejectExecutableContent(substr($bytes, $end));

                return;
            }
        } else {
            $offset = 2;
            while ($offset + 1 < $size && $bytes[$offset] === "\xFF") {
                while ($offset < $size && $bytes[$offset] === "\xFF") {
                    $offset++;
                }
                if ($offset >= $size) {
                    break;
                }
                $marker = ord($bytes[$offset++]);
                if ($marker === 0xD9) {
                    $this->rejectExecutableContent(substr($bytes, $offset));

                    return;
                }
                if ($offset + 2 > $size) {
                    break;
                }
                $length = unpack('nlength', substr($bytes, $offset, 2))['length'];
                if ($length < 2 || $length > $size - $offset) {
                    break;
                }
                if ($marker >= 0xE0 || $marker === 0xFE) {
                    $this->rejectExecutableContent(substr($bytes, $offset + 2, $length - 2));
                }
                $offset += $length;
                if ($marker === 0xDA) {
                    while (($next = strpos($bytes, "\xFF", $offset)) !== false && $next + 1 < $size) {
                        $following = ord($bytes[$next + 1]);
                        $offset = $next;
                        if ($following !== 0 && ($following < 0xD0 || $following > 0xD7)) {
                            break;
                        }
                        $offset = $next + 2;
                    }
                }
            }
        }
        $this->reject('Görsel dosyasının yapısı geçersiz.');
    }

    private function rejectExecutableContent(string $bytes): void
    {
        if (preg_match('/<\?(?:php\b|=)|<script\b|#!\s*\/.*(?:sh|python|perl)/i', $bytes)) {
            $this->reject('Çalıştırılabilir kod içeren dosyalar kabul edilmiyor.');
        }
    }

    private function inspectOfficeArchive(string $path, string $extension): void
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            $this->reject('Office dosyası okunamadı.');
        }
        try {
            $expected = $extension === 'docx' ? 'word/document.xml' : 'ppt/presentation.xml';
            if ($zip->numFiles > 3000 || $zip->locateName('[Content_Types].xml') === false || $zip->locateName($expected) === false) {
                $this->reject('Office dosyasının yapısı uzantısıyla uyuşmuyor.');
            }
            $mainType = $extension === 'docx'
                ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'
                : 'application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml';
            $types = $zip->getFromName('[Content_Types].xml', 262144);
            if ($types === false || ! str_contains($types, $mainType) || stripos($types, 'macroEnabled') !== false) {
                $this->reject('Office dosyasının içerik türü doğrulanamadı.');
            }
            $expanded = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);
                $expanded += $entry['size'];
                if ($expanded > 150 * 1024 * 1024 || $entry['size'] > 25 * 1024 * 1024
                    || preg_match('~(?:^|/)\.\.(?:/|$)|\\\\|vbaProject|activeX|embeddings/|\.(?:exe|php|js|vbs|bin)$~i', $entry['name'])
                    || ($entry['encryption_method'] ?? 0) !== 0) {
                    $this->reject('Office dosyası güvenli olmayan veya aşırı büyük içerik barındırıyor.');
                }
                if (preg_match('/\.(?:xml|rels)$/i', $entry['name'])) {
                    $xml = $zip->getFromIndex($index);
                    if ($xml === false || preg_match('/<!DOCTYPE|<!ENTITY|<\?|<script\b/i', preg_replace('/^(?:\xEF\xBB\xBF)?\s*<\?xml[^?]*\?>/i', '', $xml))) {
                        $this->reject('Office belgesinde harici veya etkin içerik bulunuyor.');
                    }
                    $document = new \DOMDocument;
                    $previous = libxml_use_internal_errors(true);
                    try {
                        if (! $document->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT)) {
                            $this->reject('Office belgesinin XML yapısı geçersiz.');
                        }
                        if ($document->doctype !== null || (new \DOMXPath($document))->query('//processing-instruction()')->length > 0) {
                            $this->reject('Office belgesinde etkin XML içeriği bulunuyor.');
                        }
                        if ($entry['name'] === $expected && $document->documentElement?->localName !== ($extension === 'docx' ? 'document' : 'presentation')) {
                            $this->reject('Office belgesinin ana içeriği geçersiz.');
                        }
                        if ($entry['name'] === $expected) {
                            $namespaces = $extension === 'docx'
                                ? ['http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'http://purl.oclc.org/ooxml/wordprocessingml/main']
                                : ['http://schemas.openxmlformats.org/presentationml/2006/main', 'http://purl.oclc.org/ooxml/presentationml/main'];
                            if (! in_array($document->documentElement?->namespaceURI, $namespaces, true)) {
                                $this->reject('Office belgesinin ad alanı geçersiz.');
                            }
                        }
                        foreach ($document->getElementsByTagNameNS('*', 'Relationship') as $relationship) {
                            if (strcasecmp($relationship->getAttribute('TargetMode'), 'External') === 0
                                && (! str_ends_with($relationship->getAttribute('Type'), '/hyperlink')
                                    || ! preg_match('~^(?:https?://|mailto:)~i', $relationship->getAttribute('Target')))) {
                                $this->reject('Office belgesinde güvenli olmayan harici içerik bulunuyor.');
                            }
                        }
                    } finally {
                        libxml_clear_errors();
                        libxml_use_internal_errors($previous);
                    }
                }
            }
        } finally {
            $zip->close();
        }
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }

    private function inspectMp4(string $path, int $size): void
    {
        $handle = fopen($path, 'rb');
        $offset = 0;
        $atoms = [];
        try {
            while ($offset < $size) {
                if (count($atoms) > 10000 || $size - $offset < 8) {
                    $this->reject('MP4 video yapısı geçersiz.');
                }
                fseek($handle, $offset);
                $header = fread($handle, 8);
                $length = unpack('N', substr($header, 0, 4))[1];
                $type = substr($header, 4, 4);
                $headerSize = 8;
                if ($length === 1) {
                    $extended = fread($handle, 8);
                    if (strlen($extended) !== 8) {
                        $this->reject('MP4 video başlığı eksik.');
                    }
                    $parts = unpack('Nhigh/Nlow', $extended);
                    if ($parts['high'] !== 0) {
                        $this->reject('MP4 video boyutu geçersiz.');
                    }
                    $length = $parts['low'];
                    $headerSize = 16;
                } elseif ($length === 0) {
                    $length = $size - $offset;
                }
                if ($length < $headerSize || $length > $size - $offset) {
                    $this->reject('MP4 video eksik veya bozuk.');
                }
                if ($type === 'moov') {
                    if ($length - $headerSize < 16) {
                        $this->reject('MP4 video akış bilgileri eksik.');
                    }
                    $metadata = fread($handle, $length - $headerSize);
                    if (! str_contains($metadata, 'trak') || ! str_contains($metadata, 'mvhd')) {
                        $this->reject('MP4 video akış bilgileri eksik.');
                    }
                }
                if ($type === 'mdat' && $length === $headerSize) {
                    $this->reject('MP4 dosyasında video verisi bulunamadı.');
                }
                $atoms[] = $type;
                $offset += $length;
            }
            if (array_diff(['ftyp', 'moov', 'mdat'], $atoms) !== []) {
                $this->reject('MP4 dosyasında gerekli video bölümleri bulunamadı.');
            }
        } finally {
            fclose($handle);
        }
    }
}
