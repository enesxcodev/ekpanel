<?php

namespace Modules\Konum\Services;

use Generator;
use RuntimeException;

class SqlDumpReader
{
    /** @return Generator<int, array<string, mixed>> */
    public function rows(string $path, string $table): Generator
    {
        $stream = fopen($path, 'rb');
        if ($stream === false) {
            throw new RuntimeException('Kaynak SQL açılamadı.');
        }
        $columns = [];
        $reading = false;
        try {
            while (($line = fgets($stream, 16777216)) !== false) {
                if (str_starts_with($line, 'INSERT INTO ')) {
                    if (! preg_match('/^INSERT INTO `'.preg_quote($table, '/').'` \\(([^)]+)\\) VALUES\\s*$/', trim($line), $matches)) {
                        throw new RuntimeException('Beklenmeyen INSERT biçimi: '.$table);
                    }
                    preg_match_all('/`([^`]+)`/', $matches[1], $names);
                    $columns = $names[1];
                    $reading = true;

                    continue;
                }
                if (! $reading) {
                    continue;
                }
                $line = trim($line);
                if (! str_starts_with($line, '(')) {
                    throw new RuntimeException('Beklenmeyen SQL veri satırı: '.$table);
                }
                $offset = 1;
                $values = [];
                do {
                    $offset += strspn($line, " \t", $offset);
                    if (($line[$offset] ?? '') === "'") {
                        $offset++;
                        $value = '';
                        while (true) {
                            $length = strcspn($line, "'\\", $offset);
                            $value .= substr($line, $offset, $length);
                            $offset += $length;
                            $character = $line[$offset] ?? null;
                            if ($character === "'") {
                                $offset++;
                                break;
                            }
                            if ($character !== '\\' || ! isset($line[$offset + 1])) {
                                throw new RuntimeException('SQL metni tamamlanmamış: '.$table);
                            }
                            $value .= $this->unescape(substr($line, $offset, 2));
                            $offset += 2;
                        }
                    } else {
                        $length = strcspn($line, ',)', $offset);
                        $token = trim(substr($line, $offset, $length));
                        $offset += $length;
                        $value = match (true) {
                            $token === 'NULL' => null,
                            preg_match('/^0x[0-9a-fA-F]+$/D', $token) === 1 => strtolower(substr($token, 2)),
                            is_numeric($token) => $token,
                            default => throw new RuntimeException('SQL değer biçimi desteklenmiyor: '.$table),
                        };
                    }
                    $values[] = $value;
                    $offset += strspn($line, " \t", $offset);
                    $delimiter = $line[$offset++] ?? null;
                    if (! in_array($delimiter, [',', ')'], true)) {
                        throw new RuntimeException('SQL ayırıcı geçersiz: '.$table);
                    }
                } while ($delimiter !== ')');
                if (count($columns) !== count($values) || ! in_array(substr($line, $offset), [',', ';'], true)) {
                    throw new RuntimeException('SQL sütun/satır sınırı geçersiz: '.$table);
                }
                yield array_combine($columns, $values);
                if (str_ends_with($line, ';')) {
                    $reading = false;
                }
            }
            if ($reading || ! feof($stream)) {
                throw new RuntimeException('Kaynak SQL eksik veya okunamadı: '.$table);
            }
        } finally {
            fclose($stream);
        }
    }

    private function unescape(string $value): string
    {
        return preg_replace_callback('/\\\\(.)/s', static fn (array $match): string => match ($match[1]) {
            '0' => "\0", 'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'Z' => "\x1a",
            default => $match[1],
        }, $value);
    }
}
