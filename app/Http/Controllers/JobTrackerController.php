<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithWorkspaceRecords;
use App\Models\JobApplication;
use App\Models\JobAttachment;
use App\Models\JobContact;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class JobTrackerController extends Controller
{
    use InteractsWithWorkspaceRecords;

    public function storeJob(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:saved,applied,interviewing,offer,rejected,withdrawn,closed'],
            'location' => ['nullable', 'string', 'max:255'],
            'work_mode' => ['nullable', 'string', 'max:50'],
            'job_url' => ['nullable', 'url', 'max:2048'],
            'applied_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'follow_up_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:3'],
        ]);

        $this->create(JobApplication::class, 'job_applications', $data + [
            'user_id' => $request->user()->id,
            'work_type' => $data['work_mode'] ?? null,
            'application_date' => $data['applied_at'] ?? null,
        ]);

        return back()->with('status', 'Job application saved.');
    }

    public function updateJob(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $data = $request->validate([
            'company' => ['required', 'string', 'max:255'], 'role' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:saved,applied,interviewing,offer,rejected,withdrawn,closed'],
            'location' => ['nullable', 'string', 'max:255'], 'work_mode' => ['nullable', 'string', 'max:50'],
            'job_url' => ['nullable', 'url', 'max:2048'], 'applied_at' => ['nullable', 'date'],
            'follow_up_at' => ['nullable', 'date'], 'priority' => ['nullable', 'integer', 'min:0', 'max:3'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
        $this->updateAvailable($job, 'job_applications', $data + ['work_type' => $data['work_mode'] ?? null, 'application_date' => $data['applied_at'] ?? null]);

        return back()->with('status', 'Application status updated.');
    }

    public function updateJobStatus(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $data = $request->validate([
            'status' => ['required', 'in:saved,applied,interviewing,offer,rejected,withdrawn,closed'],
        ]);

        $this->updateAvailable($job, 'job_applications', $data);

        return back()->with('status', 'Application marked as '.str_replace('_', ' ', $data['status']).'.');
    }

    public function storeJobContact(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'role' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'], 'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $job->contacts()->create($data);

        return back()->with('status', 'Recruiter contact saved.');
    }

    public function destroyJobContact(Request $request, JobApplication $job, JobContact $contact): RedirectResponse
    {
        $this->owns($request, $job);
        abort_unless($contact->job_application_id === $job->id, 404);
        $contact->delete();

        return back()->with('status', 'Recruiter contact removed.');
    }

    public function storeJobAttachment(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $request->validate(['attachment' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,txt,png,jpg,jpeg']]);
        $file = $request->file('attachment');
        $path = $file->store('job-attachments/'.$request->user()->id.'/'.$job->id, 'local');
        $job->attachments()->create([
            'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'original_filename' => $file->getClientOriginalName(), 'file_path' => $path, 'file_disk' => 'local',
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream', 'file_size' => $file->getSize(),
        ]);

        return back()->with('status', 'Private attachment uploaded.');
    }

    public function downloadJobAttachment(Request $request, JobApplication $job, JobAttachment $attachment)
    {
        $this->owns($request, $job);
        abort_unless($attachment->job_application_id === $job->id && Storage::disk($attachment->file_disk)->exists($attachment->file_path), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($attachment->file_disk);

        return $disk->download($attachment->file_path, $attachment->original_filename);
    }

    public function destroyJobAttachment(Request $request, JobApplication $job, JobAttachment $attachment): RedirectResponse
    {
        $this->owns($request, $job);
        abort_unless($attachment->job_application_id === $job->id, 404);
        Storage::disk($attachment->file_disk)->delete($attachment->file_path);
        $attachment->delete();

        return back()->with('status', 'Attachment removed.');
    }

    public function destroyJob(Request $request, JobApplication $job): RedirectResponse
    {
        $this->owns($request, $job);
        $job->delete();

        return back()->with('status', 'Job application removed.');
    }
}
