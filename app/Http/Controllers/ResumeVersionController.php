<?php

namespace App\Http\Controllers;

use App\Models\ResumeVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResumeVersionController extends Controller
{
    public function update(Request $request, ResumeVersion $resumeVersion): RedirectResponse
    {
        abort_unless($resumeVersion->resume->user_id === $request->user()->id, 404);

        $attributes = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'skills' => ['nullable', 'string', 'max:5000'],
            'raw_text' => ['nullable', 'string'],
        ]);

        $content = $resumeVersion->content ?: [];
        $content['summary'] = $attributes['summary'] ?? '';
        $content['skills'] = collect(explode(',', $attributes['skills'] ?? ''))
            ->map(fn (string $skill) => trim($skill))
            ->filter()
            ->values()
            ->all();
        $content['raw_text'] = $attributes['raw_text'] ?? ($content['raw_text'] ?? '');

        $resumeVersion->update([
            'label' => $attributes['label'],
            'name' => $attributes['label'],
            'change_summary' => 'User corrected extracted resume data.',
            'content' => $content,
        ]);

        $resumeVersion->resume->update(['extracted_text' => $content['raw_text'] ?: $resumeVersion->resume->extracted_text]);

        return back()->with('status', 'Resume version updated.');
    }
}
