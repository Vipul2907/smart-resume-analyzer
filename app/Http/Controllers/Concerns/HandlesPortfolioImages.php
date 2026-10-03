<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PortfolioProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandlesPortfolioImages
{
    private function projectSkills(?string $skills): array
    {
        return collect(explode(',', (string) $skills))->map(fn (string $skill) => trim($skill))->filter()->unique()->take(20)->values()->all();
    }

    private function portfolioImage(Request $request, ?PortfolioProject $existing = null): array
    {
        if (! $request->hasFile('image')) {
            return [];
        }
        $file = $request->file('image');
        if ($existing?->image_path) {
            Storage::disk($existing->image_disk ?: 'local')->delete($existing->image_path);
        }

        return [
            'image_path' => $file->store('portfolio-images/'.$request->user()->id, 'local'),
            'image_disk' => 'local',
            'image_original_filename' => $file->getClientOriginalName(),
            'image_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
        ];
    }
}
