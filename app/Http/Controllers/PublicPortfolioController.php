<?php

namespace App\Http\Controllers;

use App\Models\CareerProfile;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PublicPortfolioController extends Controller
{
    public function show(string $slug)
    {
        $profile = CareerProfile::query()
            ->where('public_slug', $slug)
            ->where('portfolio_is_public', true)
            ->with('user')
            ->firstOrFail();

        $projects = $profile->user->portfolioProjects();

        // Some existing SmartCV databases were created before the portfolio
        // ordering fields existed. Keep public pages available while the
        // compatibility migration adds those optional fields.
        // Privacy comes before backward compatibility. A database that has not
        // yet received the visibility migration must not expose every project.
        if (! Schema::hasColumn('portfolio_projects', 'visibility')) {
            $projects->whereRaw('1 = 0');
        } else {
            $projects->where('visibility', 'public');
        }
        if (Schema::hasColumn('portfolio_projects', 'is_featured')) {
            $projects->orderByDesc('is_featured');
        }
        if (Schema::hasColumn('portfolio_projects', 'display_order')) {
            $projects->orderBy('display_order');
        }

        $projects = $projects->latest()->get();
        $resume = $profile->show_resume
            ? ($profile->user->resumes()->where('is_primary', true)->first() ?: $profile->user->resumes()->latest()->first())
            : null;

        return view('portfolio.public', compact('profile', 'projects', 'resume'));
    }

    public function image(string $slug, PortfolioProject $project)
    {
        $profile = $this->profile($slug);
        abort_unless($project->user_id === $profile->user_id && $project->visibility === 'public' && $project->image_path, 404);
        $disk = $project->image_disk ?: 'local';
        abort_unless(Storage::disk($disk)->exists($project->image_path), 404);

        return Storage::disk($disk)->response($project->image_path, $project->image_original_filename ?: basename($project->image_path), [
            'Content-Type' => $project->image_mime_type ?: 'application/octet-stream',
        ]);
    }

    public function downloadResume(string $slug)
    {
        $profile = $this->profile($slug);
        abort_unless($profile->show_resume, 404);
        $resume = $profile->user->resumes()->where('is_primary', true)->first() ?: $profile->user->resumes()->latest()->first();
        abort_unless($resume && Storage::disk($resume->file_disk)->exists($resume->file_path), 404);

        return Storage::disk($resume->file_disk)->download($resume->file_path, $resume->original_filename);
    }

    private function profile(string $slug): CareerProfile
    {
        return CareerProfile::query()->where('public_slug', $slug)->where('portfolio_is_public', true)->with('user')->firstOrFail();
    }
}
