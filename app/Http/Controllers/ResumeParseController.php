<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\ResumeParser;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResumeParseController extends Controller
{
    public function store(Request $request, Resume $resume, ResumeTextExtractor $extractor, ResumeParser $parser): RedirectResponse
    {
        if (! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        abort_unless($resume->user_id === $request->user()->id, 404);

        $extracted = $extractor->extract($resume);

        $resume->update([
            'extracted_text' => $extracted['text'],
            'parse_status' => $extracted['status'],
        ]);

        $resume->versions()->update(['is_current' => false]);
        $resume->versions()->create([
            'version_number' => (int) $resume->versions()->max('version_number') + 1,
            'label' => 'Re-parsed resume',
            'change_summary' => $extracted['message'] ?: 'Refreshed structured resume data.',
            'content' => $extracted['text'] !== '' ? $parser->parse($extracted['text']) : ['message' => $extracted['message'], 'raw_text' => ''],
            'is_current' => true,
        ]);

        return back()->with('status', 'Resume parsing refreshed.');
    }
}
