<?php

namespace App\Http\Controllers;

use App\Models\Resume;
use App\Services\ResumeParser;
use App\Services\ResumeTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ResumeController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->ensureOnboarded($request)) {
            return $redirect;
        }

        return view('app', [
            'screen' => 'resumes',
            'resumes' => $request->user()->resumes()->withCount('versions')->latest()->get(),
            'selectedResume' => null,
        ]);
    }

    public function show(Request $request, Resume $resume): View|RedirectResponse
    {
        if ($redirect = $this->ensureOnboarded($request)) {
            return $redirect;
        }

        $this->authorizeUser($request, $resume);

        return view('app', [
            'screen' => 'resumes',
            'resumes' => $request->user()->resumes()->withCount('versions')->latest()->get(),
            'selectedResume' => $resume->load(['versions' => fn ($query) => $query->latest()]),
        ]);
    }

    public function store(Request $request, ResumeTextExtractor $extractor, ResumeParser $parser): RedirectResponse
    {
        if ($redirect = $this->ensureOnboarded($request)) {
            return $redirect;
        }

        $attributes = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'resume_file' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,docx,txt',
            ],
        ]);

        $file = $request->file('resume_file');
        $extension = $file->extension();
        $path = $file->storeAs('resumes/'.$request->user()->id, Str::uuid().'.'.$extension, 'local');

        try {
            $resume = DB::transaction(function () use ($request, $file, $path, $attributes): Resume {
                $isFirstResume = ! $request->user()->resumes()->exists();

                return $request->user()->resumes()->create([
                    'name' => $attributes['name'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'title' => $attributes['name'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_disk' => 'local',
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize(),
                    'parse_status' => 'pending',
                    'is_primary' => $isFirstResume,
                    'is_default' => $isFirstResume,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            report($exception);

            return back()->withInput()->with('error', 'Your resume could not be saved. Please try again.');
        }

        $parsed = $this->parseResume($resume, $extractor, $parser);

        return redirect()->route('resumes.show', $resume)->with(
            $parsed ? 'status' : 'error',
            $parsed ? 'Resume uploaded and parsed.' : 'Resume uploaded, but SmartCV could not read it yet. You can try re-parsing it.'
        );
    }

    public function update(Request $request, Resume $resume): RedirectResponse
    {
        if ($redirect = $this->ensureOnboarded($request)) {
            return $redirect;
        }

        $this->authorizeUser($request, $resume);

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $resume->update($attributes + ['title' => $attributes['name']]);

        return back()->with('status', 'Resume renamed.');
    }

    public function destroy(Request $request, Resume $resume): RedirectResponse
    {
        if ($redirect = $this->ensureOnboarded($request)) {
            return $redirect;
        }

        $this->authorizeUser($request, $resume);

        DB::transaction(function () use ($request, $resume): void {
            Storage::disk($resume->file_disk)->delete($resume->file_path);
            $wasPrimary = $resume->is_primary;
            $resume->delete();

            if ($wasPrimary) {
                $request->user()->resumes()->latest()->first()?->update(['is_primary' => true]);
            }
        });

        return redirect()->route('resumes')->with('status', 'Resume deleted safely.');
    }

    public function markPrimary(Request $request, Resume $resume): RedirectResponse
    {
        if ($redirect = $this->ensureOnboarded($request)) {
            return $redirect;
        }

        $this->authorizeUser($request, $resume);

        DB::transaction(function () use ($request, $resume): void {
            $request->user()->resumes()->update(['is_primary' => false, 'is_default' => false]);
            $resume->update(['is_primary' => true, 'is_default' => true]);
        });

        return back()->with('status', 'Primary resume updated.');
    }

    public function download(Request $request, Resume $resume)
    {
        $this->authorizeUser($request, $resume);
        abort_unless(Storage::disk($resume->file_disk)->exists($resume->file_path), 404);

        return Storage::disk($resume->file_disk)->download($resume->file_path, $resume->original_filename);
    }

    private function parseResume(Resume $resume, ResumeTextExtractor $extractor, ResumeParser $parser): bool
    {
        try {
            $extracted = $extractor->extract($resume);
            $content = $extracted['text'] !== '' ? $parser->parse($extracted['text']) : [
                'contact' => [],
                'summary' => '',
                'work_experience' => [],
                'education' => [],
                'skills' => [],
                'projects' => [],
                'certificates' => [],
                'raw_text' => '',
                'message' => $extracted['message'],
            ];

            $resume->update([
                'extracted_text' => $extracted['text'],
                'parse_status' => $extracted['status'],
            ]);

            $resume->versions()->update(['is_current' => false]);
            $resume->versions()->create([
                'user_id' => $resume->user_id,
                'version_number' => (int) $resume->versions()->max('version_number') + 1,
                'label' => 'Parsed import',
                'name' => 'Parsed import',
                'change_summary' => $extracted['message'] ?: 'Created from uploaded resume text.',
                'content' => $content,
                'is_current' => true,
                'is_active' => true,
            ]);

            return true;
        } catch (\Throwable $exception) {
            $resume->update(['parse_status' => 'failed']);
            report($exception);

            return false;
        }
    }

    private function authorizeUser(Request $request, Resume $resume): void
    {
        abort_unless($resume->user_id === $request->user()->id, 404);
    }

    private function ensureOnboarded(Request $request): ?RedirectResponse
    {
        if (! $request->user()->onboarding_completed_at) {
            return redirect()->route('onboarding.show');
        }

        return null;
    }
}
