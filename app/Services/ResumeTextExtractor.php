<?php

namespace App\Services;

use App\Models\Resume;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class ResumeTextExtractor
{
    /**
     * @return array{status: string, text: string, message: string|null}
     */
    public function extract(Resume $resume): array
    {
        $contents = Storage::disk($resume->file_disk)->get($resume->file_path);
        $extension = strtolower(pathinfo($resume->original_filename, PATHINFO_EXTENSION));

        $text = match ($extension) {
            'txt' => $this->normalizeText($contents),
            'docx' => $this->extractDocx($resume),
            'pdf' => $this->extractPdfText($contents),
            default => '',
        };

        if ($text !== '') {
            return ['status' => 'parsed', 'text' => $text, 'message' => null];
        }

        if ($extension === 'pdf' && str_contains($contents, '/Subtype /Image')) {
            return [
                'status' => 'image_only',
                'text' => '',
                'message' => 'This looks like an image-only PDF. OCR can be added later, but there is no readable text yet.',
            ];
        }

        return [
            'status' => 'empty',
            'text' => '',
            'message' => 'SmartCV could not find readable text in this document.',
        ];
    }

    private function extractDocx(Resume $resume): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is required to read DOCX files.');
        }

        $zip = new ZipArchive();
        $path = Storage::disk($resume->file_disk)->path($resume->file_path);

        if ($zip->open($path) !== true) {
            return '';
        }

        $parts = [];

        foreach (['word/document.xml', 'word/footnotes.xml', 'word/endnotes.xml'] as $entry) {
            $xml = $zip->getFromName($entry);

            if ($xml !== false) {
                $parts[] = preg_replace('/<w:tab\/>/', ' ', $xml);
            }
        }

        $zip->close();

        return $this->normalizeText(html_entity_decode(strip_tags(implode("\n", $parts))));
    }

    private function extractPdfText(string $contents): string
    {
        $streams = [$contents];

        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $contents, $matches)) {
            foreach ($matches[1] as $stream) {
                $streams[] = $stream;

                $decoded = @gzuncompress(trim($stream));

                if (is_string($decoded)) {
                    $streams[] = $decoded;
                }
            }
        }

        $pieces = [];

        foreach ($streams as $stream) {
            if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)\s*T[Jj]/s', $stream, $textMatches)) {
                foreach ($textMatches[0] as $token) {
                    preg_match('/\((.*)\)\s*T[Jj]/s', $token, $value);
                    $pieces[] = isset($value[1]) ? stripcslashes($value[1]) : '';
                }
            }

            if (preg_match_all('/\[(.*?)\]\s*TJ/s', $stream, $arrayMatches)) {
                foreach ($arrayMatches[1] as $arrayToken) {
                    if (preg_match_all('/\((?:\\\\.|[^\\\\)])*\)/s', $arrayToken, $strings)) {
                        foreach ($strings[0] as $stringToken) {
                            $pieces[] = stripcslashes(trim($stringToken, '()'));
                        }
                    }
                }
            }
        }

        return $this->normalizeText(implode(' ', $pieces));
    }

    private function normalizeText(string $text): string
    {
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\R{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
