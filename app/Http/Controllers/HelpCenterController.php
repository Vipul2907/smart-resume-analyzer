<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HelpCenterController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->string('q')->toString());
        $guides = collect($this->guides());
        $faqs = collect($this->faqs());

        if ($query !== '') {
            $needle = mb_strtolower($query);
            $matches = fn (array $item): bool => str_contains(mb_strtolower(implode(' ', $item)), $needle);
            $guides = $guides->filter($matches)->values();
            $faqs = $faqs->filter($matches)->values();
        }

        $requests = $request->user()->supportRequests()->latest()->limit(5)->get();

        return view('help.index', compact('guides', 'faqs', 'query', 'requests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:account,resume,ai,jobs,interview,technical,other'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $request->user()->supportRequests()->create($data + ['status' => 'open']);

        return back()->with('status', 'Your support request was saved. The support team will be able to review it from the SmartCV admin area.');
    }

    /** @return array<int, array<string, string>> */
    private function guides(): array
    {
        return [
            ['category' => 'Getting started', 'title' => 'Build your first resume', 'description' => 'Create a resume from scratch, choose a template, and save targeted versions for different roles.', 'route' => 'resumes.builder.create', 'action' => 'Create a resume'],
            ['category' => 'Resume quality', 'title' => 'Improve ATS readiness', 'description' => 'Upload or select a resume, then use the ATS check to spot missing sections and improvement opportunities.', 'route' => 'ats', 'action' => 'Open ATS optimization'],
            ['category' => 'Job search', 'title' => 'Match your resume to a job', 'description' => 'Paste a job description or choose a live job listing to see a private resume-to-role match.', 'route' => 'match', 'action' => 'Open job match'],
            ['category' => 'Applications', 'title' => 'Track every opportunity', 'description' => 'Save job applications, update their stage, add recruiter contacts, and set follow-up dates.', 'route' => 'jobs', 'action' => 'Open job tracker'],
            ['category' => 'Interview practice', 'title' => 'Prepare with focused questions', 'description' => 'Create a practice session, answer each question, save progress, and receive an honest final score.', 'route' => 'interviews', 'action' => 'Open interview lab'],
            ['category' => 'Learning', 'title' => 'Turn goals into a learning path', 'description' => 'Create a learning path based on your target role, saved skills, job matches, and career goals.', 'route' => 'learning-paths.index', 'action' => 'Open learning paths'],
            ['category' => 'Portfolio', 'title' => 'Publish work carefully', 'description' => 'Keep projects private by default. Choose exactly what appears in your public portfolio and resume profile.', 'route' => 'portfolio', 'action' => 'Open portfolio'],
            ['category' => 'Privacy', 'title' => 'Control your account data', 'description' => 'Change your password, manage AI consent, download personal data, or permanently delete your account.', 'route' => 'settings', 'action' => 'Open settings'],
        ];
    }

    /** @return array<int, array<string, string>> */
    private function faqs(): array
    {
        return [
            ['question' => 'Is SmartCV really free?', 'answer' => 'Yes. SmartCV is designed as a free career workspace. There is no payment screen or credit-card requirement in this version.'],
            ['question' => 'Who can see my resume and job applications?', 'answer' => 'Only you can see them by default. Your files and workspace entries stay private. Portfolio content becomes public only when you turn on public sharing.'],
            ['question' => 'What does SmartCV send to Groq?', 'answer' => 'Only the resume text and job description needed for the analysis you explicitly request. Every AI action requires your consent checkbox.'],
            ['question' => 'Why did an AI analysis fail?', 'answer' => 'Usually the Groq API key, selected model, internet connection, or provider rate limit needs attention. Check your .env Groq settings and try again.'],
            ['question' => 'Can I use more than one resume?', 'answer' => 'Yes. Create or upload as many targeted resumes as you need. You can choose any saved resume for analysis and set one as your primary resume.'],
            ['question' => 'How do I save a report as PDF?', 'answer' => 'Open a printable career or recruiter-ready report, press Ctrl+P, then choose Save as PDF in your browser print dialog.'],
            ['question' => 'Can I remove my data?', 'answer' => 'Yes. Settings includes a personal-data export and a protected account deletion action. Account deletion requires your password and the word DELETE.'],
            ['question' => 'Do live job listings guarantee availability?', 'answer' => 'No. Listings come from an external public job source and can change or close. Always confirm the role on the employer application page.'],
        ];
    }
}
