<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\ResumeParser;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ResumeParseController extends Controller
{
    public function store(Request $request, Resume $resume, ResumeTextExtractor $extractor, ResumeParser $parser): RedirectResponse
    {
        if (! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        abort_unless($resume->user_id === $request->user()->id, 404);

        try {
            $extracted = $extractor->extract($resume);

            $resume->update([
                'extracted_text' => $extracted['text'],
                'parse_status' => $extracted['status'],
            ]);

            $resume->versions()->update(['is_current' => false]);
            $version = [
                'user_id' => $resume->user_id,
                'version_number' => (int) $resume->versions()->max('version_number') + 1,
                'label' => 'Re-parsed resume',
                'name' => 'Re-parsed resume',
                'change_summary' => $extracted['message'] ?: 'Refreshed structured resume data.',
                'content' => $extracted['text'] !== '' ? $parser->parse($extracted['text']) : ['message' => $extracted['message'], 'raw_text' => ''],
                'is_current' => true,
                'is_active' => true,
            ];

            $resume->versions()->create(array_intersect_key($version, array_flip(Schema::getColumnListing('resume_versions'))));
        } catch (\Throwable $exception) {
            $resume->update(['parse_status' => 'failed']);
            report($exception);

            return back()->with('error', 'SmartCV could not read this file. Please upload a text-based PDF, DOCX, or TXT resume.');
        }

        return back()->with('status', 'Resume parsing refreshed.');
    }
}
