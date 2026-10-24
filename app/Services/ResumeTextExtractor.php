<?php

namespace App\Services;

use App\Models\Resume;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
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
            'pdf' => $this->extractPdf($resume, $contents),
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

        $zip = new ZipArchive;
        $path = Storage::disk($resume->file_disk)->path($resume->file_path);

        if ($zip->open($path) !== true) {
            return '';
        }

        $parts = [];

        foreach (['word/document.xml', 'word/footnotes.xml', 'word/endnotes.xml'] as $entry) {
            $xml = $zip->getFromName($entry);

            if ($xml !== false) {
                $parts[] = $this->extractDocxXml($xml);
            }
        }

        $zip->close();

        return $this->normalizeText(html_entity_decode(strip_tags(implode("\n", $parts))));
    }

    private function extractDocxXml(string $xml): string
    {
        $document = new DOMDocument;
        $document->preserveWhiteSpace = true;

        if (@$document->loadXML($xml, LIBXML_NONET)) {
            $paragraphs = (new DOMXPath($document))->query('//*[local-name()="p"]');

            if ($paragraphs !== false && $paragraphs->length > 0) {
                $text = [];

                foreach ($paragraphs as $paragraph) {
                    $line = $this->readWordNode($paragraph);

                    if (trim($line) !== '') {
                        $text[] = $line;
                    }
                }

                return implode("\n", $text);
            }
        }

        // Keep a tolerant fallback for unusual or malformed DOCX XML.
        $xml = preg_replace('/>\s+</', '><', $xml) ?? $xml;
        $xml = preg_replace('/<[^>]*:tab\b[^>]*\/>/i', ' ', $xml) ?? $xml;
        $xml = preg_replace('/<[^>]*:(?:br|cr)\b[^>]*\/>/i', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/[^>]*:tc\s*>/i', "\t", $xml) ?? $xml;
        $xml = preg_replace('/<\/[^>]*:tr\s*>/i', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<\/[^>]*:p\s*>/i', "\n", $xml) ?? $xml;

        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function readWordNode(DOMNode $node): string
    {
        if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
            return $node->nodeValue ?? '';
        }

        $name = strtolower($node->localName ?? '');

        if (in_array($name, ['tab', 'ptab'], true)) {
            return ' ';
        }

        if (in_array($name, ['br', 'cr'], true)) {
            return "\n";
        }

        $text = '';

        foreach ($node->childNodes as $child) {
            $text .= $this->readWordNode($child);
        }

        if ($name === 'tc') {
            $text .= "\t";
        }

        return $text;
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

    private function extractPdf(Resume $resume, string $contents): string
    {
        try {
            $parser = new PdfParser;
            $text = $parser->parseFile(Storage::disk($resume->file_disk)->path($resume->file_path))->getText();

            if ($normalized = $this->normalizeText($text)) {
                return $normalized;
            }
        } catch (\Throwable) {
            // The fallback below is useful for a few simple PDFs that have malformed metadata.
        }

        return $this->extractPdfText($contents);
    }

    private function normalizeText(string $text): string
    {
        // PDF and DOCX text runs can join a location directly to a phone number.
        $text = preg_replace('/(?<=\p{L})\(/u', ' (', $text) ?? $text;
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\R{3,}/', "\n\n", $text) ?? $text;

        return trim($text);
    }
}
