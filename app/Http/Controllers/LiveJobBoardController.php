<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class LiveJobBoardController extends Controller
{
    private const API_URL = 'https://www.arbeitnow.com/api/job-board-api';

    private const MAX_RESPONSE_BYTES = 6 * 1024 * 1024;

    private const MAX_JOBS_PER_PAGE = 30;

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
            // Version the key because older entries contain the full provider response.
            $cacheKey = 'arbeitnow.jobs.page.v2.'.$page;
            $payload = Cache::get($cacheKey);

            if (! is_array($payload)) {
                $temporaryFile = tempnam(sys_get_temp_dir(), 'smartcv-jobs-');

                if ($temporaryFile === false) {
                    throw new RuntimeException('Could not prepare a temporary job response file.');
                }

                try {
                    $response = Http::acceptJson()
                        ->timeout(12)
                        ->retry(1, 250)
                        ->withOptions([
                            'sink' => $temporaryFile,
                            'on_headers' => static function (ResponseInterface $response): void {
                                $length = $response->getHeaderLine('Content-Length');

                                if ($length !== '' && (int) $length > self::MAX_RESPONSE_BYTES) {
                                    throw new RuntimeException('The live job response is too large to process safely.');
                                }
                            },
                            'progress' => static function (float $downloadTotal, float $downloaded): void {
                                if ($downloaded > self::MAX_RESPONSE_BYTES) {
                                    throw new RuntimeException('The live job response is too large to process safely.');
                                }
                            },
                        ])
                        ->get(self::API_URL, ['page' => $page]);

                    if ($response->successful()) {
                        $fileSize = @filesize($temporaryFile);

                        if (is_int($fileSize) && $fileSize > self::MAX_RESPONSE_BYTES) {
                            throw new RuntimeException('The live job response is too large to process safely.');
                        }

                        $body = @file_get_contents($temporaryFile);

                        // Laravel's HTTP fake does not write its response to Guzzle's sink file.
                        if (! is_string($body) || $body === '') {
                            $body = $response->body();
                        }

                        if (strlen($body) > self::MAX_RESPONSE_BYTES) {
                            throw new RuntimeException('The live job response is too large to process safely.');
                        }

                        $providerPayload = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

                        if (is_array($providerPayload) && is_array($providerPayload['data'] ?? null)) {
                            $normalisedJobs = collect($providerPayload['data'])
                                ->filter(fn ($job) => is_array($job) && $this->isTechnologyOpening($job))
                                ->map(fn (array $job) => $this->normaliseJob($job))
                                ->take(self::MAX_JOBS_PER_PAGE)
                                ->values()
                                ->all();

                            $payload = [
                                'meta' => [
                                    'current_page' => data_get($providerPayload, 'meta.current_page'),
                                    'next_page' => data_get($providerPayload, 'meta.next_page'),
                                ],
                                'data' => $normalisedJobs,
                            ];

                            Cache::put($cacheKey, $payload, now()->addMinutes(10));
                        }
                    }
                } finally {
                    @unlink($temporaryFile);
                }
            }

            if (is_array($payload)) {
                $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
                $jobs = collect($payload['data'] ?? [])
                    ->filter(fn ($job) => is_array($job) && $this->isTechnologyOpening($job))
                    ->filter(fn (array $job) => $query === '' || $this->matchesQuery($job, $query))
                    ->take(self::MAX_JOBS_PER_PAGE)
                    ->values();
            } else {
                $error = 'Live jobs are temporarily unavailable. Please try again in a moment.';
            }
        } catch (RequestException) {
            $error = 'Live jobs are temporarily unavailable. Please try again in a moment.';
        } catch (ConnectionException) {
            $error = 'SmartCV could not reach the live job source. Check your internet connection and try again.';
        } catch (JsonException|RuntimeException) {
            $error = 'Live jobs are temporarily unavailable. Please try again in a moment.';
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
            'software',
            'developer',
            'engineer',
            'frontend',
            'front-end',
            'backend',
            'back-end',
            'full stack',
            'fullstack',
            'devops',
            'data ',
            'machine learning',
            'ai ',
            'cyber',
            'security',
            'cloud',
            'qa ',
            'quality assurance',
            'product manager',
            'ux',
            'ui ',
            'wordpress',
            'php',
            'javascript',
            'typescript',
            'python',
            'java',
            'react',
            'laravel',
        ]);
    }

    /** @param array<string, mixed> $job
     * @return array{title: string, company: string, location: string, tags: array<int, string>, description: string, url: string, remote: bool, created_at: int|null}
     */
    private function normaliseJob(array $job): array
    {
        $description = $this->cleanDescription($job['description'] ?? '');

        return [
            'title' => trim((string) ($job['title'] ?? 'Untitled opening')),
            'company' => trim((string) ($job['company_name'] ?? 'Unknown company')),
            'location' => trim((string) ($job['location'] ?? '')),
            'tags' => collect($job['tags'] ?? [])->filter(fn ($tag) => is_string($tag))->map(fn (string $tag) => trim($tag))->filter()->take(8)->values()->all(),
            'description' => Str::limit($description, 5000, ''),
            'url' => (string) ($job['url'] ?? ''),
            'remote' => (bool) ($job['remote'] ?? false),
            'created_at' => isset($job['created_at']) && is_numeric($job['created_at']) ? (int) $job['created_at'] : null,
        ];
    }

    /** @param array{title: string, company: string, location: string, tags: array<int, string>, description: string, url: string, remote: bool, created_at: int|null} $job */
    private function matchesQuery(array $job, string $query): bool
    {
        return Str::contains(Str::lower(implode(' ', [
            $job['title'],
            $job['company'],
            $job['location'],
            implode(' ', $job['tags']),
            $job['description'],
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
