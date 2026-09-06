<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AccountSettingsController extends Controller
{
    public function index(Request $request): View
    {
        $preferences = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);

        return view('settings.index', compact('preferences'));
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email_reminders' => ['nullable', 'boolean'],
            'weekly_career_review' => ['nullable', 'boolean'],
            'in_app_reminders' => ['nullable', 'boolean'],
            'ai_processing_enabled' => ['nullable', 'boolean'],
            'retain_ai_history' => ['nullable', 'boolean'],
        ]);

        $preferences = UserPreference::firstOrCreate(['user_id' => $request->user()->id]);
        $previousRetainHistory = $preferences->retain_ai_history;
        $preferences->update([
            'email_reminders' => $request->boolean('email_reminders'),
            'weekly_career_review' => $request->boolean('weekly_career_review'),
            'in_app_reminders' => $request->boolean('in_app_reminders'),
            'ai_processing_enabled' => $request->boolean('ai_processing_enabled'),
            'retain_ai_history' => $request->boolean('retain_ai_history'),
        ]);

        if ($previousRetainHistory && ! $preferences->retain_ai_history) {
            $request->user()->aiAnalyses()->delete();
        }

        return back()->with('status', $preferences->retain_ai_history
            ? 'Privacy and notification preferences saved.'
            : 'Preferences saved. Your saved AI analysis history has been removed.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = $request->user();
        $user->update(['password' => Hash::make($data['password'])]);
        $this->forgetOtherSessions($request);

        return back()->with('status', 'Password changed. Other signed-in browsers have been signed out.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $user = $request->user();
        $this->deletePrivateFiles($user);
        $this->forgetOtherSessions($request);
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Your SmartCV account and private workspace data have been deleted.');
    }

    private function forgetOtherSessions(Request $request): void
    {
        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }
    }

    private function deletePrivateFiles(object $user): void
    {
        $this->deleteFiles($user->resumes()->get(), 'file_path', 'file_disk');
        $this->deleteFiles($user->privateDocuments()->get(), 'file_path', 'file_disk');
        $this->deleteFiles($user->skills()->get(), 'certificate_path', 'certificate_disk');
        $this->deleteFiles($user->interviewSessions()->get(), 'recording_path', 'recording_disk');
        $this->deleteFiles($user->portfolioProjects()->get(), 'image_path', 'image_disk');
        $this->deleteFiles($user->jobApplications()->with('attachments')->get()->flatMap->attachments, 'file_path', 'file_disk');
    }

    private function deleteFiles(iterable $records, string $pathColumn, string $diskColumn): void
    {
        foreach ($records as $record) {
            $path = $record->{$pathColumn} ?? null;
            if ($path) {
                Storage::disk($record->{$diskColumn} ?: 'local')->delete($path);
            }
        }
    }
}
