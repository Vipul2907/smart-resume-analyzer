<?php

namespace Tests\Feature;

use App\Models\Resume;
use App\Models\User;
use App\Services\ResumeTextExtractor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class ResumeTextExtractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_docx_parser_keeps_paragraph_and_phone_boundaries(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->markTestSkipped('The PHP zip extension is required to read DOCX files.');
        }

        Storage::fake('local');
        $user = $this->user();
        $path = 'resumes/'.$user->id.'/resume.docx';
        $temporaryFile = tempnam(sys_get_temp_dir(), 'smartcv-docx-');
        $this->assertNotFalse($temporaryFile);

        try {
            $zip = new ZipArchive;
            $this->assertSame(true, $zip->open($temporaryFile, ZipArchive::OVERWRITE));
            $zip->addFromString('word/document.xml', <<<'XML'
                <?xml version="1.0" encoding="UTF-8"?>
                <w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>
                    <w:p><w:r><w:t>Bangalore, India(555) 123-4567</w:t></w:r></w:p>
                    <w:p><w:r><w:t>Next paragraph</w:t></w:r></w:p>
                </w:body></w:document>
                XML);
            $zip->close();
            Storage::disk('local')->put($path, (string) file_get_contents($temporaryFile));
        } finally {
            @unlink($temporaryFile);
        }

        $result = app(ResumeTextExtractor::class)->extract(new Resume([
            'file_disk' => 'local',
            'file_path' => $path,
            'original_filename' => 'resume.docx',
        ]));

        $this->assertSame('parsed', $result['status']);
        $this->assertStringContainsString('Bangalore, India (555) 123-4567', $result['text']);
        $this->assertStringContainsString("123-4567\nNext paragraph", $result['text']);
    }

    public function test_pdf_parser_separates_a_phone_number_from_a_location(): void
    {
        Storage::fake('local');
        $user = $this->user();
        $path = 'resumes/'.$user->id.'/resume.pdf';
        Storage::disk('local')->put($path, <<<'PDF'
            %PDF-1.4
            1 0 obj
            <<>>
            stream
            BT (Bangalore, India\(555\) 123-4567) Tj ET
            endstream
            endobj
            %%EOF
            PDF);

        $result = app(ResumeTextExtractor::class)->extract(new Resume([
            'file_disk' => 'local',
            'file_path' => $path,
            'original_filename' => 'resume.pdf',
        ]));

        $this->assertSame('parsed', $result['status']);
        $this->assertStringContainsString('Bangalore, India (555) 123-4567', $result['text']);
    }

    public function test_resume_details_link_targets_the_visible_details_panel(): void
    {
        $user = $this->user();
        $resume = $user->resumes()->create([
            'name' => 'Primary resume',
            'original_filename' => 'resume.txt',
            'file_path' => 'resumes/'.$user->id.'/resume.txt',
            'mime_type' => 'text/plain',
            'file_size' => 10,
        ]);

        $this->actingAs($user)->get(route('resumes'))
            ->assertOk()
            ->assertSee(route('resumes.show', $resume).'#resume-details', false);

        $this->actingAs($user)->get(route('resumes.show', $resume))
            ->assertOk()
            ->assertSee('id="resume-details"', false);
    }

    private function user(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
    }
}