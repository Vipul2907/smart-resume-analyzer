<?php

namespace App\Http\Controllers;

use App\Models\JobSearch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JobDiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $keywords = trim((string) $request->string('keywords'));
        $location = trim((string) $request->string('location'));
        $workMode = trim((string) $request->string('work_mode'));
        $searchText = trim(implode(' ', array_filter([$keywords, $location, $workMode])));

        return view('workspace.discovery', [
            'keywords' => $keywords,
            'location' => $location,
            'workMode' => $workMode,
            'searchText' => $searchText,
            'searches' => $user->jobSearches()->latest()->get(),
            'jobs' => $user->jobApplications()->latest()->limit(10)->get(['id', 'company', 'role', 'status', 'job_url']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'keywords' => ['required', 'string', 'max:500'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_mode' => ['nullable', 'in:remote,hybrid,on-site'],
            'experience_level' => ['nullable', 'in:internship,entry,mid,senior,leadership'],
            'frequency' => ['required', 'in:daily,weekly'],
            'is_alert_enabled' => ['nullable', 'boolean'],
        ]);
        $request->user()->jobSearches()->create($data + ['is_alert_enabled' => (bool) ($data['is_alert_enabled'] ?? false)]);

        return redirect()->route('discover', ['q' => $data['keywords']])->with('status', 'Saved job alert created.');
    }

    public function update(Request $request, JobSearch $search): RedirectResponse
    {
        $this->owns($request, $search);
        $data = $request->validate([
            'frequency' => ['required', 'in:daily,weekly'],
            'is_alert_enabled' => ['nullable', 'boolean'],
        ]);
        $search->update($data + ['is_alert_enabled' => (bool) ($data['is_alert_enabled'] ?? false)]);

        return back()->with('status', 'Saved search alert updated.');
    }

    public function destroy(Request $request, JobSearch $search): RedirectResponse
    {
        $this->owns($request, $search);
        $search->delete();

        return back()->with('status', 'Saved search removed.');
    }

    public function open(Request $request, JobSearch $search): RedirectResponse
    {
        $this->owns($request, $search);
        $search->update(['last_opened_at' => now()]);

        return redirect()->route('discover', ['q' => $search->queryText()]);
    }

    public function addToTracker(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'job_url' => ['nullable', 'url', 'max:2048'],
        ]);
        $request->user()->jobApplications()->create($data + [
            'status' => 'saved',
        ]);

        return redirect()->route('jobs')->with('status', 'Discovered opportunity added to your Job Tracker.');
    }

    private function owns(Request $request, JobSearch $search): void
    {
        abort_unless($search->user_id === $request->user()->id, 404);
    }
}
