<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LiveJobBoardController extends Controller
{
    private const API_URL = 'https://www.arbeitnow.com/api/job-board-api';

    /**
     * Display current technology-related openings supplied by Arbeitnow.
     */
    public function index(Request $request)
    {
        $attributes = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'resume' => ['nullable', 'integer'],
        ]);

        $page = $attributes['page'] ?? 1;
        $query = trim((string) ($attributes['q'] ?? ''));
        $resumes = $request->user()->resumes()->orderByDesc('is_primary')->latest()->get();
        $searches = $request->user()->jobSearches()->latest()->get();
        $requestedResumeId = $attributes['resume'] ?? null;
        $selectedResume = $requestedResumeId
            ? $resumes->firstWhere('id', $requestedResumeId)
            : $resumes->firstWhere('is_primary', true);
        $selectedResume = $selectedResume ?: $resumes->first();
        $jobs = collect();
        $meta = [];
        $error = null;

        try {
            $response = Http::acceptJson()
                ->timeout(12)
                ->retry(2, 250)
                ->get(self::API_URL, ['page' => $page]);

            if ($response->successful()) {
                $payload = $response->json();
                $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
                $jobs = collect($payload['data'] ?? [])
                    ->filter(fn ($job) => is_array($job) && $this->isTechnologyOpening($job))
                    ->map(fn (array $job) => $this->normaliseJob($job))
                    ->filter(fn (array $job) => $query === '' || $this->matchesQuery($job, $query))
                    ->values();
            } else {
                $error = 'The live job source is temporarily unavailable. Please try again in a moment.';
            }
        } catch (ConnectionException) {
            $error = 'SmartCV could not reach the live job source. Check your internet connection and try again.';
        }

        return view('jobs.live-board', compact('error', 'jobs', 'meta', 'page', 'query', 'resumes', 'searches', 'selectedResume'));
    }

    /** @param array<string, mixed> $job */
    private function isTechnologyOpening(array $job): bool
    {
        $searchable = Str::lower(implode(' ', [
            (string) ($job['title'] ?? ''),
            (string) ($job['description'] ?? ''),
            implode(' ', is_array($job['tags'] ?? null) ? $job['tags'] : []),
        ]));

        return Str::contains($searchable, [
            'software', 'developer', 'engineer', 'frontend', 'front-end', 'backend', 'back-end',
            'full stack', 'fullstack', 'devops', 'data ', 'machine learning', 'ai ', 'cyber',
            'security', 'cloud', 'qa ', 'quality assurance', 'product manager', 'ux', 'ui ',
            'wordpress', 'php', 'javascript', 'typescript', 'python', 'java', 'react', 'laravel',
        ]);
    }

    /** @param array<string, mixed> $job
     *  @return array{title: string, company: string, location: string, tags: array<int, string>, description: string, url: string, remote: bool, created_at: int|null}
     */
    private function normaliseJob(array $job): array
    {
        $description = $this->cleanDescription($job['description'] ?? '');

        return [
            'title' => trim((string) ($job['title'] ?? 'Untitled opening')),
            'company' => trim((string) ($job['company_name'] ?? 'Unknown company')),
            'location' => trim((string) ($job['location'] ?? '')),
            'tags' => collect($job['tags'] ?? [])->filter(fn ($tag) => is_string($tag))->map(fn (string $tag) => trim($tag))->filter()->take(8)->values()->all(),
            'description' => Str::limit($description, 11500, ''),
            'url' => (string) ($job['url'] ?? ''),
            'remote' => (bool) ($job['remote'] ?? false),
            'created_at' => isset($job['created_at']) && is_numeric($job['created_at']) ? (int) $job['created_at'] : null,
        ];
    }

    /** @param array{title: string, company: string, location: string, tags: array<int, string>, description: string, url: string, remote: bool, created_at: int|null} $job */
    private function matchesQuery(array $job, string $query): bool
    {
        return Str::contains(Str::lower(implode(' ', [
            $job['title'], $job['company'], $job['location'], implode(' ', $job['tags']), $job['description'],
        ])), Str::lower($query));
    }

    private function cleanDescription(mixed $description): string
    {
        if (! is_string($description)) {
            return '';
        }

        // Arbeitnow can encode markup once or twice. Decode it before stripping tags.
        $decoded = html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decoded = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = html_entity_decode(strip_tags($decoded), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
