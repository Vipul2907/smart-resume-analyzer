<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\InteractsWithWorkspaceRecords;
use App\Models\Skill;
use App\Models\SkillMilestone;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SkillController extends Controller
{
    use InteractsWithWorkspaceRecords;

    public function storeSkill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'category' => ['nullable', 'string', 'max:255'],
            'proficiency' => ['nullable', 'integer', 'min:0', 'max:100'], 'years_experience' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'is_priority' => ['nullable', 'boolean'], 'evidence' => ['nullable', 'string', 'max:1000'],
            'certificate' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp'],
        ]);
        $certificate = [];
        if ($request->hasFile('certificate')) {
            $file = $request->file('certificate');
            $certificate = [
                'certificate_original_filename' => $file->getClientOriginalName(),
                'certificate_path' => $file->store('skill-certificates/'.$request->user()->id, 'local'),
                'certificate_disk' => 'local',
                'certificate_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'certificate_size' => $file->getSize(),
            ];
        }
        unset($data['certificate']);
        $this->create(Skill::class, 'skills', $data + $certificate + ['user_id' => $request->user()->id, 'is_learning' => false]);

        return back()->with('status', 'Skill saved.');
    }

    public function destroySkill(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        if ($skill->certificate_path) {
            Storage::disk($skill->certificate_disk ?: 'local')->delete($skill->certificate_path);
        }
        $skill->delete();

        return back()->with('status', 'Skill removed.');
    }

    public function updateSkill(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        $data = $request->validate([
            'proficiency' => ['required', 'integer', 'min:0', 'max:100'],
            'target_proficiency' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_priority' => ['nullable', 'boolean'],
            'evidence' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->updateAvailable($skill, 'skills', $data + ['is_priority' => (bool) ($data['is_priority'] ?? false)]);

        return back()->with('status', 'Skill progress updated.');
    }

    public function storeSkillMilestone(Request $request, Skill $skill): RedirectResponse
    {
        $this->owns($request, $skill);
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'target_date' => ['nullable', 'date']]);
        $skill->milestones()->create($data);

        return back()->with('status', 'Learning milestone saved.');
    }

    public function updateSkillMilestone(Request $request, Skill $skill, SkillMilestone $milestone): RedirectResponse
    {
        $this->owns($request, $skill);
        abort_unless($milestone->skill_id === $skill->id, 404);
        $data = $request->validate(['status' => ['required', 'in:planned,in_progress,completed']]);
        $milestone->update($data + ['completed_at' => $data['status'] === 'completed' ? now() : null]);

        return back()->with('status', 'Learning milestone updated.');
    }

    public function destroySkillMilestone(Request $request, Skill $skill, SkillMilestone $milestone): RedirectResponse
    {
        $this->owns($request, $skill);
        abort_unless($milestone->skill_id === $skill->id, 404);
        $milestone->delete();

        return back()->with('status', 'Learning milestone removed.');
    }

    public function downloadSkillCertificate(Request $request, Skill $skill)
    {
        $this->owns($request, $skill);
        $diskName = $skill->certificate_disk ?: 'local';
        abort_unless($skill->certificate_path && Storage::disk($diskName)->exists($skill->certificate_path), 404);

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);

        return $disk->download($skill->certificate_path, $skill->certificate_original_filename ?: 'certificate');
    }
}
