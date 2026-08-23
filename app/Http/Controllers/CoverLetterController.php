<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class CoverLetterController extends Controller
{
    public function index(Request $request): View
    {
        return view('cover-letters.index', [
            'letters' => $request->user()->coverLetters()->with(['jobApplication', 'resume'])->latest()->get(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('cover-letters.editor', $this->editorData($request, null));
    }

    public function store(Request $request): RedirectResponse
    {
        $letter = $request->user()->coverLetters()->create($this->validated($request));

        return redirect()->route('cover-letters.edit', $letter)->with('status', 'Cover letter created. It is private to your account.');
    }

    public function edit(Request $request, CoverLetter $coverLetter): View
    {
        $this->owns($request, $coverLetter);

        return view('cover-letters.editor', $this->editorData($request, $coverLetter));
    }

    public function update(Request $request, CoverLetter $coverLetter): RedirectResponse
    {
        $this->owns($request, $coverLetter);
        $coverLetter->update($this->validated($request));

        return back()->with('status', 'Cover letter saved.');
    }

    public function duplicate(Request $request, CoverLetter $coverLetter): RedirectResponse
    {
        $this->owns($request, $coverLetter);
        $copy = $coverLetter->replicate(['last_exported_at']);
        $copy->title = $coverLetter->title.' copy';
        $copy->status = 'draft';
        $copy->save();

        return redirect()->route('cover-letters.edit', $copy)->with('status', 'A separate copy is ready for your next application.');
    }

    public function destroy(Request $request, CoverLetter $coverLetter): RedirectResponse
    {
        $this->owns($request, $coverLetter);
        $coverLetter->delete();

        return redirect()->route('cover-letters.index')->with('status', 'Cover letter removed.');
    }

    public function preview(Request $request, CoverLetter $coverLetter): View
    {
        $this->owns($request, $coverLetter);

        return view('cover-letters.preview', ['letter' => $coverLetter]);
    }

    public function downloadText(Request $request, CoverLetter $coverLetter)
    {
        $this->owns($request, $coverLetter);
        $coverLetter->update(['last_exported_at' => now(), 'status' => 'ready']);

        return response($this->plainText($coverLetter), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.Str::slug($coverLetter->title ?: 'cover-letter').'.txt"',
        ]);
    }

    public function downloadDocx(Request $request, CoverLetter $coverLetter)
    {
        $this->owns($request, $coverLetter);
        abort_unless(class_exists(ZipArchive::class), 503, 'DOCX export requires the PHP Zip extension. Enable it in XAMPP and try again.');
        $file = tempnam(sys_get_temp_dir(), 'smartcv_letter_');
        $zip = new ZipArchive();
        abort_unless($zip->open($file, ZipArchive::CREATE) === true, 500, 'Could not prepare the DOCX export.');
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/document.xml', $this->docxXml($coverLetter));
        $zip->close();
        $coverLetter->update(['last_exported_at' => now(), 'status' => 'ready']);

        return response()->download($file, Str::slug($coverLetter->title ?: 'cover-letter').'.docx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])->deleteFileAfterSend(true);
    }

    private function editorData(Request $request, ?CoverLetter $letter): array
    {
        return [
            'letter' => $letter,
            'jobs' => $request->user()->jobApplications()->latest()->get(['id', 'company', 'role']),
            'resumes' => $request->user()->resumes()->orderByDesc('is_primary')->latest()->get(['id', 'name', 'is_primary']),
            'templates' => ['modern' => 'Modern and direct', 'classic' => 'Classic and formal', 'warm' => 'Warm and personal'],
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'template' => ['required', 'in:modern,classic,warm'],
            'job_application_id' => ['nullable', 'integer'],
            'resume_id' => ['nullable', 'integer'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'opening' => ['nullable', 'string', 'max:3000'],
            'body' => ['required', 'string', 'max:12000'],
            'closing' => ['nullable', 'string', 'max:3000'],
            'signature_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:draft,ready'],
        ]);
        abort_unless(! $data['job_application_id'] || $request->user()->jobApplications()->whereKey($data['job_application_id'])->exists(), 404);
        abort_unless(! $data['resume_id'] || $request->user()->resumes()->whereKey($data['resume_id'])->exists(), 404);

        return $data;
    }

    private function owns(Request $request, CoverLetter $letter): void
    {
        abort_unless($letter->user_id === $request->user()->id, 404);
    }

    private function plainText(CoverLetter $letter): string
    {
        return implode("\n\n", array_filter([
            $letter->subject ? 'Subject: '.$letter->subject : null,
            $letter->recipient_name ?: null,
            $letter->company_name ?: null,
            $letter->opening,
            $letter->body,
            $letter->closing,
            $letter->signature_name,
        ], fn ($value) => filled($value)));
    }

    private function docxXml(CoverLetter $letter): string
    {
        $paragraphs = collect(preg_split('/\R/', $this->plainText($letter)) ?: [])
            ->map(fn ($line) => '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>')
            ->implode('');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$paragraphs.'<w:sectPr/></w:body></w:document>';
    }
}
