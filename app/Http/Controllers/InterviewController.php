<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsInterviewQuestions;
use App\Http\Controllers\Concerns\InteractsWithWorkspaceRecords;
use App\Models\InterviewSession;
use App\Services\GroqAiService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InterviewController extends Controller
{
    use BuildsInterviewQuestions, InteractsWithWorkspaceRecords;

    public function storeInterview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_role' => ['nullable', 'string', 'max:255'],
            'session_type' => ['required', 'in:general,technical,behavioral,hr,leadership,case_study'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:180'],
            'job_application_id' => ['nullable', 'integer'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'reminder_at' => ['nullable', 'date'],
        ]);
        if ($data['job_application_id'] ?? null) {
            abort_unless($request->user()->jobApplications()->whereKey($data['job_application_id'])->exists(), 404);
        }

        $questions = $this->interviewQuestions($data['session_type'], $data['target_role'] ?? 'your target role');
        $this->create(InterviewSession::class, 'interview_sessions', $data + [
            'user_id' => $request->user()->id,
            'status' => 'in_progress',
            'type' => $data['session_type'],
            'questions' => $questions,
            'questions_count' => count($questions),
            'completed_questions' => 0,
        ]);

        return back()->with('status', 'Interview practice is ready. Answer the questions, save your progress, then mark it complete.');
    }

    public function saveInterviewResponses(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        $data = $request->validate(['answers' => ['nullable', 'array'], 'answers.*' => ['nullable', 'string', 'max:5000']]);
        $answers = collect($data['answers'] ?? [])->map(fn ($answer) => trim((string) $answer))->all();
        $this->updateAvailable($interview, 'interview_sessions', [
            'responses' => $answers,
            'completed_questions' => collect($answers)->filter()->count(),
            'status' => 'in_progress',
            'started_at' => $interview->started_at ?: now(),
        ]);

        return back()->with('status', 'Your interview answers have been saved.');
    }

    public function completeInterview(Request $request, InterviewSession $interview, GroqAiService $groq): RedirectResponse
    {
        $this->owns($request, $interview);
        $answers = collect($interview->responses ?? [])->map(fn ($answer) => trim((string) $answer))->filter();
        if ($answers->isEmpty()) {
            return back()->withErrors(['answers' => 'Save at least one written answer before calculating your readiness score.']);
        }
        if ($answers->contains(fn (string $answer) => ! $this->isMeaningfulInterviewAnswer($answer))) {
            return back()->withErrors(['answers' => 'Your answers need real sentences with specific details. Random letters or very short text cannot be scored.']);
        }

        try {
            $feedback = $groq->evaluateInterviewResponses($interview);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['answers' => 'SmartCV could not analyse your answers right now. Check your Groq setup and try again.']);
        }
        $this->updateAvailable($interview, 'interview_sessions', [
            'status' => 'completed', 'completed_at' => now(), 'score' => $feedback['score'], 'overall_score' => $feedback['score'], 'feedback' => $feedback,
        ]);

        return back()->with('status', 'Interview session completed. Your readiness score was calculated from your saved answers.');
    }

    public function storeInterviewRecording(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        $request->validate(['recording' => ['required', 'file', 'max:102400', 'mimes:mp3,m4a,wav,webm,mp4,mov']]);
        $file = $request->file('recording');
        if ($interview->recording_path) {
            Storage::disk($interview->recording_disk ?: 'local')->delete($interview->recording_path);
        }
        $this->updateAvailable($interview, 'interview_sessions', [
            'recording_path' => $file->store('interview-recordings/'.$request->user()->id.'/'.$interview->id, 'local'),
            'recording_disk' => 'local',
            'recording_original_filename' => $file->getClientOriginalName(),
            'recording_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'recording_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Practice recording saved privately.');
    }

    public function downloadInterviewRecording(Request $request, InterviewSession $interview)
    {
        $this->owns($request, $interview);
        $diskName = $interview->recording_disk ?: 'local';
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);
        abort_unless($interview->recording_path && $disk->exists($interview->recording_path), 404);

        return $disk->download($interview->recording_path, $interview->recording_original_filename ?: 'interview-recording');
    }

    public function playInterviewRecording(Request $request, InterviewSession $interview)
    {
        $this->owns($request, $interview);
        $diskName = $interview->recording_disk ?: 'local';
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);
        abort_unless($interview->recording_path && $disk->exists($interview->recording_path), 404);

        return $disk->response(
            $interview->recording_path,
            $interview->recording_original_filename,
            ['Content-Type' => $interview->recording_mime_type ?: 'application/octet-stream', 'Content-Disposition' => 'inline']
        );
    }

    public function destroyInterview(Request $request, InterviewSession $interview): RedirectResponse
    {
        $this->owns($request, $interview);
        if ($interview->recording_path) {
            Storage::disk($interview->recording_disk ?: 'local')->delete($interview->recording_path);
        }
        $interview->delete();

        return back()->with('status', 'Interview session removed.');
    }
}
