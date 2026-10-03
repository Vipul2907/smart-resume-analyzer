<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesPortfolioImages;
use App\Http\Controllers\Concerns\InteractsWithWorkspaceRecords;
use App\Models\CareerProfile;
use App\Models\PortfolioProject;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    use HandlesPortfolioImages, InteractsWithWorkspaceRecords;

    public function storeProject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'], 'role' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:2048'], 'repository_url' => ['nullable', 'url', 'max:2048'],
            'skills' => ['nullable', 'string', 'max:1000'], 'outcome' => ['nullable', 'string', 'max:2000'],
            'case_study' => ['nullable', 'string', 'max:10000'], 'visibility' => ['required', 'in:private,public'],
            'is_featured' => ['nullable', 'boolean'], 'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $image = $this->portfolioImage($request);
        $this->create(PortfolioProject::class, 'portfolio_projects', array_merge($data, $image, [
            'user_id' => $request->user()->id, 'status' => 'completed', 'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(5)),
            'short_description' => $data['tagline'] ?? null, 'github_url' => $data['repository_url'] ?? null,
            'skills' => $this->projectSkills($data['skills'] ?? null), 'is_featured' => (bool) ($data['is_featured'] ?? false),
        ]));

        return back()->with('status', 'Portfolio project saved. It stays private unless you chose public visibility.');
    }

    public function updateProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->owns($request, $project);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'], 'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'], 'role' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:2048'], 'repository_url' => ['nullable', 'url', 'max:2048'],
            'skills' => ['nullable', 'string', 'max:1000'], 'outcome' => ['nullable', 'string', 'max:2000'],
            'case_study' => ['nullable', 'string', 'max:10000'], 'visibility' => ['required', 'in:private,public'],
            'is_featured' => ['nullable', 'boolean'], 'image' => ['nullable', 'image', 'max:5120'],
        ]);
        $image = $this->portfolioImage($request, $project);
        $this->updateAvailable($project, 'portfolio_projects', array_merge($data, $image, [
            'short_description' => $data['tagline'] ?? null, 'github_url' => $data['repository_url'] ?? null,
            'skills' => $this->projectSkills($data['skills'] ?? null), 'is_featured' => (bool) ($data['is_featured'] ?? false),
        ]));

        return back()->with('status', 'Portfolio project updated.');
    }

    public function updatePortfolioSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'public_slug' => ['required', 'string', 'min:3', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:career_profiles,public_slug,'.optional($request->user()->careerProfile)->id],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'portfolio_is_public' => ['nullable', 'boolean'], 'show_contact_email' => ['nullable', 'boolean'], 'show_resume' => ['nullable', 'boolean'],
        ]);
        $profile = CareerProfile::updateOrCreate(
            ['user_id' => $request->user()->id],
            $data + [
                'portfolio_is_public' => (bool) ($data['portfolio_is_public'] ?? false),
                'show_contact_email' => (bool) ($data['show_contact_email'] ?? false),
                'show_resume' => (bool) ($data['show_resume'] ?? false),
            ]
        );

        return back()->with('status', $profile->portfolio_is_public ? 'Your public portfolio is live.' : 'Your public portfolio is private.');
    }

    public function destroyProject(Request $request, PortfolioProject $project): RedirectResponse
    {
        $this->owns($request, $project);
        if ($project->image_path) {
            Storage::disk($project->image_disk ?: 'local')->delete($project->image_path);
        }
        $project->delete();

        return back()->with('status', 'Portfolio project removed.');
    }

    public function showProjectImage(Request $request, PortfolioProject $project)
    {
        $this->owns($request, $project);
        $diskName = $project->image_disk ?: 'local';
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($diskName);
        abort_unless($project->image_path && $disk->exists($project->image_path), 404);

        return $disk->response($project->image_path, $project->image_original_filename ?: basename($project->image_path), [
            'Content-Type' => $project->image_mime_type ?: 'application/octet-stream',
        ]);
    }
}
