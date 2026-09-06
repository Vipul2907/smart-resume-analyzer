<?php

namespace App\Http\Controllers;

use App\Models\PrivateDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use ZipArchive;

class DocumentVaultController extends Controller
{
    public function index(Request $request)
    {
        return view('documents.index', [
            'documents' => $request->user()->privateDocuments()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'document' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,png,jpg,jpeg,webp'],
        ]);
        $file = $data['document'];
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->store('private-documents/'.$request->user()->id, 'local');
        $text = $this->extractText($path, $extension);

        $request->user()->privateDocuments()->create([
            'name' => trim($data['name'] ?? '') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_disk' => 'local',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
            'category' => $extension === 'pdf' ? 'pdf' : ($extension === 'docx' || $extension === 'doc' ? 'document' : ($extension === 'txt' ? 'text' : 'image')),
            'extracted_text' => $text,
        ]);

        return back()->with('status', $text !== '' ? 'Private document uploaded and text extracted.' : 'Private document uploaded. This file has no readable text preview.');
    }

    public function download(Request $request, PrivateDocument $document)
    {
        $this->owns($request, $document);
        abort_unless(Storage::disk($document->file_disk ?: 'local')->exists($document->file_path), 404);

        return Storage::disk($document->file_disk ?: 'local')->download($document->file_path, $document->original_filename);
    }

    public function destroy(Request $request, PrivateDocument $document): RedirectResponse
    {
        $this->owns($request, $document);
        Storage::disk($document->file_disk ?: 'local')->delete($document->file_path);
        $document->delete();

        return back()->with('status', 'Private document removed.');
    }

    private function extractText(string $path, string $extension): string
    {
        $disk = Storage::disk('local');

        try {
            if ($extension === 'txt') {
                return $this->normalise((string) $disk->get($path));
            }
            if ($extension === 'pdf') {
                return $this->normalise((new Parser())->parseFile($disk->path($path))->getText());
            }
            if ($extension === 'docx') {
                $archive = new ZipArchive();
                if ($archive->open($disk->path($path)) === true) {
                    $xml = $archive->getFromName('word/document.xml') ?: '';
                    $archive->close();

                    return $this->normalise(html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
                }
            }
        } catch (\Throwable) {
            // The file remains safely stored even when a preview cannot be extracted.
        }

        return '';
    }

    private function normalise(string $text): string
    {
        return Str::limit(trim((string) preg_replace('/\s+/u', ' ', $text)), 12000, '');
    }

    private function owns(Request $request, PrivateDocument $document): void
    {
        abort_unless($document->user_id === $request->user()->id, 404);
    }
}
