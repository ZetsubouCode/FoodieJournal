<?php

namespace App\Services;

class ImageMetadata
{
    /** Remove metadata without recompressing pixels. Keep only JPEG orientation for correct viewing. */
    public function clean(string $bytes, string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => $this->jpeg($bytes),
            'image/png' => $this->png($bytes),
            'image/webp' => $this->webp($bytes),
            default => throw new \RuntimeException('Unsupported image'),
        };
    }

    private function jpeg(string $bytes): string
    {
        if (substr($bytes, 0, 2) !== "\xff\xd8") {
            throw new \RuntimeException('Invalid JPEG');
        }
        $out = substr($bytes, 0, 2);
        $at = 2;
        $length = strlen($bytes);
        while ($at < $length) {
            if (ord($bytes[$at]) !== 255) {
                throw new \RuntimeException('Invalid JPEG marker');
            }
            $start = $at;
            while ($at < $length && ord($bytes[$at]) === 255) {
                $at++;
            }
            if ($at >= $length) {
                break;
            } $marker = ord($bytes[$at++]);
            if ($marker === 0xDA || $marker === 0xD9) {
                return $out.substr($bytes, $start);
            }
            if ($marker === 0x01 || ($marker >= 0xD0 && $marker <= 0xD7)) {
                $out .= substr($bytes, $start, $at - $start);

                continue;
            }
            if ($at + 2 > $length) {
                throw new \RuntimeException('Truncated JPEG');
            }
            $size = unpack('n', substr($bytes, $at, 2))[1];
            if ($size < 2 || $at + $size > $length) {
                throw new \RuntimeException('Truncated JPEG segment');
            }
            // Strip EXIF/XMP/IPTC/comments. ICC APP2 is retained for color accuracy.
            if (! in_array($marker, [0xE1, 0xED, 0xFE])) {
                $out .= substr($bytes, $start, $at + $size - $start);
            }
            $at += $size;
        }
        throw new \RuntimeException('Incomplete JPEG');
    }

    private function png(string $bytes): string
    {
        $out = substr($bytes, 0, 8);
        $at = 8;
        $length = strlen($bytes);
        while ($at + 12 <= $length) {
            $size = unpack('N', substr($bytes, $at, 4))[1];
            $type = substr($bytes, $at + 4, 4);
            if ($at + $size + 12 > $length) {
                throw new \RuntimeException('Invalid PNG');
            }
            if (! in_array($type, ['eXIf', 'tEXt', 'iTXt', 'zTXt', 'tIME'])) {
                $out .= substr($bytes, $at, $size + 12);
            }
            $at += $size + 12;
            if ($type === 'IEND') {
                return $out;
            }
        }
        throw new \RuntimeException('Incomplete PNG');
    }

    private function webp(string $bytes): string
    {
        $chunks = '';
        $at = 12;
        $length = strlen($bytes);
        while ($at + 8 <= $length) {
            $type = substr($bytes, $at, 4);
            $size = unpack('V', substr($bytes, $at + 4, 4))[1];
            if ($at + 8 + $size > $length) {
                throw new \RuntimeException('Invalid WebP');
            }
            $chunk = substr($bytes, $at, 8 + $size + ($size % 2));
            if ($type === 'VP8X' && $size > 0) {
                $chunk[8] = chr(ord($chunk[8]) & ~0x0C);
            }
            if (! in_array($type, ['EXIF', 'XMP '])) {
                $chunks .= $chunk;
            }
            $at += 8 + $size + ($size % 2);
        }

        return 'RIFF'.pack('V', strlen($chunks) + 4).'WEBP'.$chunks;
    }
}
