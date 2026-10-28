<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use ZipArchive;

class ResumeBuilderController extends Controller
{
    public function create(Request $request): View
    {
        return view('resumes.builder', ['resume' => null, 'content' => $this->emptyContent($request)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $content = $this->contentFromRequest($request);
        $path = 'resumes/'.$request->user()->id.'/'.Str::uuid().'.json';

        Storage::disk('local')->put($path, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $resume = DB::transaction(function () use ($request, $data, $content, $path): Resume {
            $first = ! $request->user()->resumes()->exists();
            $resume = $request->user()->resumes()->create($this->columns('resumes', [
                'name' => $data['name'], 'title' => $data['name'], 'original_filename' => Str::slug($data['name']).'.smartcv.json',
                'file_path' => $path, 'file_disk' => 'local', 'mime_type' => 'application/json', 'file_size' => strlen(json_encode($content)),
                'extracted_text' => $this->plainText($content), 'parse_status' => 'ready', 'is_primary' => $first, 'is_default' => $first,
            ]));
            $this->saveVersion($resume, $content, 'Initial draft', 'Created in the SmartCV resume builder.');

            return $resume;
        });

        return redirect()->route('resumes.builder.edit', $resume)->with('status', 'Your new resume draft is ready.');
    }

    public function edit(Request $request, Resume $resume): View
    {
        $this->owns($request, $resume);

        return view('resumes.builder', ['resume' => $resume, 'content' => $this->normalise($resume->currentVersion()?->content ?: $this->emptyContent($request))]);
    }

    public function update(Request $request, Resume $resume): RedirectResponse
    {
        $this->owns($request, $resume);
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'version_label' => ['nullable', 'string', 'max:255']]);
        $content = $this->contentFromRequest($request);
        $path = $resume->file_path ?: 'resumes/'.$request->user()->id.'/'.Str::uuid().'.json';
        Storage::disk('local')->put($path, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        DB::transaction(function () use ($resume, $data, $content, $path): void {
            $resume->update($this->columns('resumes', ['name' => $data['name'], 'title' => $data['name'], 'file_path' => $path, 'file_size' => strlen(json_encode($content)), 'extracted_text' => $this->plainText($content), 'parse_status' => 'ready']));
            $current = $resume->currentVersion();
            if ($current) {
                $current->update($this->columns('resume_versions', ['label' => $data['version_label'] ?: $current->label, 'name' => $data['version_label'] ?: $current->label, 'change_summary' => 'Updated in the SmartCV resume builder.', 'content' => $content]));
            } else {
                $this->saveVersion($resume, $content, $data['version_label'] ?: 'Resume draft', 'Created in the SmartCV resume builder.');
            }
        });

        return back()->with('status', 'Resume saved securely.');
    }

    public function duplicate(Request $request, Resume $resume): RedirectResponse
    {
        $this->owns($request, $resume);
        $content = $this->normalise($resume->currentVersion()?->content ?: $this->emptyContent($request));
        $path = 'resumes/'.$request->user()->id.'/'.Str::uuid().'.json';
        Storage::disk('local')->put($path, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $copy = DB::transaction(function () use ($request, $resume, $content, $path): Resume {
            $copy = $request->user()->resumes()->create($this->columns('resumes', ['name' => $resume->name.' copy', 'title' => $resume->name.' copy', 'original_filename' => Str::slug($resume->name.' copy').'.smartcv.json', 'file_path' => $path, 'file_disk' => 'local', 'mime_type' => 'application/json', 'file_size' => strlen(json_encode($content)), 'extracted_text' => $this->plainText($content), 'parse_status' => 'ready', 'is_primary' => false, 'is_default' => false]));
            $this->saveVersion($copy, $content, 'Copied draft', 'Copied from '.$resume->name.'.');

            return $copy;
        });

        return redirect()->route('resumes.builder.edit', $copy)->with('status', 'A separate targeted copy has been created.');
    }

    public function newVersion(Request $request, Resume $resume): RedirectResponse
    {
        $this->owns($request, $resume);
        $data = $request->validate(['label' => ['required', 'string', 'max:255']]);
        $content = $this->normalise($resume->currentVersion()?->content ?: $this->emptyContent($request));
        DB::transaction(function () use ($resume, $content, $data): void {
            $this->saveVersion($resume, $content, $data['label'], 'Saved as a new targeted version.');
        });

        return back()->with('status', 'New resume version saved.');
    }

    public function preview(Request $request, Resume $resume): View
    {
        $this->owns($request, $resume);

        return view('resumes.preview', ['resume' => $resume, 'content' => $this->normalise($resume->currentVersion()?->content ?: [])]);
    }

    public function exportDocx(Request $request, Resume $resume)
    {
        $this->owns($request, $resume);
        abort_unless(class_exists(ZipArchive::class), 503, 'DOCX export requires the PHP Zip extension. Enable it in XAMPP and try again.');
        $content = $this->normalise($resume->currentVersion()?->content ?: []);
        $file = tempnam(sys_get_temp_dir(), 'smartcv_');
        $zip = new ZipArchive;
        abort_unless($zip->open($file, ZipArchive::CREATE) === true, 500, 'Could not prepare the DOCX export.');
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');
        $zip->addFromString('word/document.xml', $this->docxXml($content));
        $zip->close();

        return response()->download($file, Str::slug($resume->name ?: 'resume').'.docx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])->deleteFileAfterSend(true);
    }

    private function contentFromRequest(Request $request): array
    {
        $request->validate([
            'personal' => ['required', 'array'],
            'personal.name' => ['required', 'string', 'max:255'],
            'personal.email' => ['required', 'email', 'max:255'],
            'summary' => ['required', 'string', 'max:6000'],
            'education' => ['required', 'array', 'min:1'],
            'education.*.degree' => ['required', 'string', 'max:255'],
            'education.*.school' => ['required', 'string', 'max:255'],
            'declaration_enabled' => ['required', 'boolean'],
            'declaration_name' => ['exclude_unless:declaration_enabled,1', 'required', 'string', 'max:255'],
            'template' => ['nullable', 'in:classic,modern,executive'],
            'accent_color' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'page_length' => ['nullable', 'in:one,two'],
        ]);
        $clean = fn ($items, array $keys) => collect($items ?: [])->map(function ($item) use ($keys) {
            $item = is_array($item) ? $item : [];
            $row = [];
            foreach ($keys as $key) {
                $row[$key] = trim((string) ($item[$key] ?? ''));
            }

            return $row;
        })->filter(fn ($row) => collect($row)->filter()->isNotEmpty())->values()->all();
        $experience = $clean($request->input('experience'), ['title', 'company', 'location', 'start', 'end', 'highlights_text']);
        $experience = collect($experience)->map(function ($item) {
            $item['highlights'] = collect(preg_split('/\r\n|\r|\n/', $item['highlights_text'] ?: ''))->map(fn ($line) => trim($line))->filter()->values()->all();
            unset($item['highlights_text']);

            return $item;
        })->all();

        return $this->normalise(['personal' => array_map(fn ($value) => trim((string) $value), $request->input('personal', [])), 'summary' => trim((string) $request->input('summary')), 'experience' => $experience, 'education' => $clean($request->input('education'), ['degree', 'school', 'location', 'start', 'end', 'details']), 'skills' => collect(explode(',', (string) $request->input('skills')))->map(fn ($s) => trim($s))->filter()->values()->all(), 'projects' => $clean($request->input('projects'), ['name', 'role', 'link', 'details']), 'certifications' => $clean($request->input('certifications'), ['name', 'issuer', 'date']), 'awards' => $clean($request->input('awards'), ['name', 'issuer', 'date']), 'languages' => $clean($request->input('languages'), ['name', 'level']), 'interests' => collect(explode(',', (string) $request->input('interests')))->map(fn ($s) => trim($s))->filter()->values()->all(), 'custom_sections' => $clean($request->input('custom_sections'), ['title', 'content']), 'declaration_enabled' => $request->boolean('declaration_enabled'), 'declaration_name' => trim((string) $request->input('declaration_name')), 'settings' => ['template' => $request->input('template', 'classic'), 'accent_color' => $request->input('accent_color', '#7c3aed'), 'font_family' => $request->input('font_family', 'Inter'), 'page_length' => $request->input('page_length', 'one')]]);
    }

    private function emptyContent(Request $request): array
    {
        return $this->normalise(['personal' => ['name' => $request->user()->name, 'email' => $request->user()->email, 'phone' => '', 'location' => '', 'website' => '', 'linkedin' => ''], 'education' => [['degree' => '', 'school' => '', 'location' => '', 'start' => '', 'end' => '', 'details' => '']]]);
    }

    private function normalise(array $content): array
    {
        $defaults = ['personal' => ['name' => '', 'email' => '', 'phone' => '', 'location' => '', 'website' => '', 'linkedin' => ''], 'summary' => '', 'experience' => [], 'education' => [], 'skills' => [], 'projects' => [], 'certifications' => [], 'awards' => [], 'languages' => [], 'interests' => [], 'custom_sections' => [], 'declaration_enabled' => false, 'declaration_name' => '', 'settings' => ['template' => 'classic', 'accent_color' => '#7c3aed', 'font_family' => 'Inter', 'page_length' => 'one']];

        return array_replace_recursive($defaults, $content);
    }

    private function saveVersion(Resume $resume, array $content, string $label, string $summary): void
    {
        $resume->versions()->update(['is_current' => false]);
        $resume->versions()->create($this->columns('resume_versions', ['resume_id' => $resume->id, 'user_id' => $resume->user_id, 'version_number' => ((int) $resume->versions()->max('version_number')) + 1, 'label' => $label, 'name' => $label, 'change_summary' => $summary, 'content' => $content, 'is_current' => true, 'is_active' => true]));
    }

    private function owns(Request $request, Resume $resume): void
    {
        abort_unless($resume->user_id === $request->user()->id, 404);
    }

    private function columns(string $table, array $attributes): array
    {
        return array_intersect_key($attributes, array_flip(Schema::getColumnListing($table)));
    }

    private function plainText(array $content): string
    {
        $sections = [$content['personal']['name'] ?? '', collect($content['personal'] ?? [])->except('name')->filter()->implode(' · '), $content['summary'] ?? ''];
        foreach (['experience', 'education', 'projects', 'certifications', 'awards', 'languages', 'custom_sections'] as $key) {
            foreach ($content[$key] ?? [] as $item) {
                $sections[] = collect($item)->flatten()->filter()->implode(' · ');
            }
        }
        $sections[] = collect($content['skills'] ?? [])->implode(', ');
        $sections[] = collect($content['interests'] ?? [])->implode(', ');
        if (! empty($content['declaration_enabled'])) {
            $sections[] = 'I hereby declare that the information provided above is true and correct to the best of my knowledge and belief.';
            $sections[] = $content['declaration_name'] ?? '';
        }

        return trim(collect($sections)->filter()->implode("\n\n"));
    }

    private function docxXml(array $content): string
    {
        $template = in_array($content['settings']['template'] ?? 'classic', ['classic', 'modern', 'executive'], true)
            ? $content['settings']['template']
            : 'classic';
        $accent = ltrim((string) ($content['settings']['accent_color'] ?? '#7c3aed'), '#');
        if (! preg_match('/^[A-Fa-f0-9]{6}$/', $accent)) {
            $accent = '7C3AED';
        }
        $accent = strtoupper($accent);
        $font = $template === 'executive' ? 'Georgia' : 'Arial';
        $paragraphs = [];
        $addParagraph = function (string $text, string $role = 'body') use (&$paragraphs, $template, $accent, $font): void {
            if ($text === '') {
                return;
            }

            $size = match ($role) {
                'title' => $template === 'modern' ? 44 : 40,
                'section' => $template === 'executive' ? 24 : 22,
                'meta' => 18,
                default => 20,
            };
            $color = match ([$template, $role]) {
                ['modern', 'title'], ['modern', 'section'] => $accent,
                ['executive', 'title'], ['executive', 'section'] => '24344D',
                default => '1F2937',
            };
            $paragraphStyle = '';
            if ($role === 'section') {
                $paragraphStyle .= $template === 'modern'
                    ? '<w:pBdr><w:left w:val="single" w:sz="18" w:space="8" w:color="'.$accent.'"/></w:pBdr><w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/>'
                    : '<w:pBdr><w:bottom w:val="single" w:sz="'.($template === 'executive' ? '8' : '4').'" w:space="3" w:color="'.($template === 'executive' ? $accent : '64748B').'"/></w:pBdr>';
            }
            $paragraphStyle .= '<w:spacing w:after="'.($role === 'section' ? '100' : '60').'" w:line="'.($template === 'executive' ? '260' : '240').'" w:lineRule="auto"/>';
            if ($template === 'executive' && in_array($role, ['title', 'meta'], true)) {
                $paragraphStyle .= '<w:jc w:val="center"/>';
            }
            $bold = in_array($role, ['title', 'section'], true) ? '<w:b/>' : '';
            $runStyle = '<w:rFonts w:ascii="'.$font.'" w:hAnsi="'.$font.'"/><w:sz w:val="'.$size.'"/><w:color w:val="'.$color.'"/>'.$bold;
            $paragraphs[] = '<w:p><w:pPr>'.$paragraphStyle.'</w:pPr><w:r><w:rPr>'.$runStyle.'</w:rPr><w:t xml:space="preserve">'.htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</w:t></w:r></w:p>';
        };

        $addParagraph((string) ($content['personal']['name'] ?? 'Resume'), 'title');
        $addParagraph('CONTACT INFORMATION', 'section');
        $addParagraph(collect($content['personal'] ?? [])->except('name')->filter()->implode(' | '), 'meta');
        $addParagraph('PROFESSIONAL SUMMARY', 'section');
        $addParagraph((string) ($content['summary'] ?? ''));
        foreach (['education' => 'EDUCATION', 'experience' => 'EXPERIENCE', 'projects' => 'PROJECTS', 'certifications' => 'CERTIFICATIONS', 'awards' => 'AWARDS', 'languages' => 'LANGUAGES'] as $key => $title) {
            if (! empty($content[$key])) {
                $addParagraph($title, 'section');
                foreach ($content[$key] as $item) {
                    $addParagraph(collect($item)->map(fn ($value) => is_array($value) ? implode('; ', $value) : $value)->filter()->implode(' | '));
                }
            }
        }
        if (! empty($content['skills'])) {
            $addParagraph('SKILLS', 'section');
            $addParagraph(implode(', ', $content['skills']));
        }
        if (! empty($content['interests'])) {
            $addParagraph('INTERESTS', 'section');
            $addParagraph(implode(', ', $content['interests']));
        }
        foreach ($content['custom_sections'] ?? [] as $section) {
            $addParagraph((string) ($section['title'] ?? 'Additional information'), 'section');
            $addParagraph((string) ($section['content'] ?? ''));
        }
        if (! empty($content['declaration_enabled'])) {
            $addParagraph('DECLARATION', 'section');
            $addParagraph('I hereby declare that the information provided above is true and correct to the best of my knowledge and belief.');
            $addParagraph((string) ($content['declaration_name'] ?? ''), 'meta');
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.implode('', $paragraphs).$this->a4SectionXml().'</w:body></w:document>';
    }

    private function a4SectionXml(): string
    {
        return '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1020" w:right="907" w:bottom="1020" w:left="907"/></w:sectPr>';
    }
}
