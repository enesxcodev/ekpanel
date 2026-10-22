<?php

namespace Modules\Konum\Services;

use RuntimeException;

class PolygonGeometry
{
    /** @return array{type: string, coordinates: array<int, array<int, array{float, float}>>, srid: int} */
    public function decode(string $hex): array
    {
        if (strlen($hex) % 2 !== 0 || ! ctype_xdigit($hex)) {
            throw new RuntimeException('Polygon hex geçersiz.');
        }
        $binary = hex2bin($hex);
        $size = strlen($binary);
        if ($size < 13 || ord($binary[4]) !== 1 || unpack('V', substr($binary, 5, 4))[1] !== 3) {
            throw new RuntimeException('Beklenen MySQL SRID + little-endian Polygon bulunamadı.');
        }
        $rings = unpack('V', substr($binary, 9, 4))[1];
        $offset = 13;
        $coordinates = [];
        for ($ring = 0; $ring < $rings; $ring++) {
            if ($offset + 4 > $size) {
                throw new RuntimeException('Polygon halka sınırı geçersiz.');
            }
            $points = unpack('V', substr($binary, $offset, 4))[1];
            $offset += 4;
            if ($points < 4 || $points > intdiv($size - $offset, 16)) {
                throw new RuntimeException('Polygon nokta sayısı geçersiz.');
            }
            $positions = [];
            for ($point = 0; $point < $points; $point++) {
                $pair = array_values(unpack('e2', substr($binary, $offset, 16)));
                if (! is_finite($pair[0]) || ! is_finite($pair[1])) {
                    throw new RuntimeException('Polygon koordinatı geçersiz.');
                }
                $positions[] = $pair;
                $offset += 16;
            }
            if ($positions[0] !== $positions[array_key_last($positions)]) {
                throw new RuntimeException('Polygon halkası kapalı değil.');
            }
            $coordinates[] = $positions;
        }
        if ($offset !== $size || $rings === 0) {
            throw new RuntimeException('Polygon uzunluğu geçersiz.');
        }

        return ['type' => 'Polygon', 'coordinates' => $coordinates, 'srid' => unpack('V', substr($binary, 0, 4))[1]];
    }
}
