<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user->name }} · Admin · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#070b18] text-zinc-100">
    @include('components.skip-link')
    <div class="min-h-screen">
        <x-workspace-sidebar active-screen="admin" />
        <div class="min-h-screen lg:pl-64">
            <header
                class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
                <a href="{{ route('admin.index') }}" class="font-bold lg:hidden">SMART<span
                        class="text-violet-300">CV</span></a>
                <p class="hidden text-sm text-zinc-500 sm:block">Administrator · private account data</p>
                <a href="{{ route('admin.index') }}" class="text-sm font-medium text-cyan-200">Back to users</a>
            </header>

            <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9" id="main-content" tabindex="-1">
                <p class="eyebrow">Administrator account view</p>
                <div class="mt-2 flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-semibold tracking-tight">{{ $user->name }}</h1>
                        <p class="mt-2 text-sm text-zinc-400">{{ $user->email }}</p>
                        <p class="mt-2 text-xs text-zinc-500">
                            Joined {{ $user->created_at?->format('M j, Y g:i A') ?? 'Unknown' }}
                            · {{ $user->email_verified_at ? 'Email verified' : 'Email not verified' }}
                            · {{ $user->is_admin ? 'Administrator' : 'Member' }}
                        </p>
                    </div>
                    <a class="btn btn-secondary" href="{{ route('admin.index', ['q' => $user->email]) }}">Back to search results</a>
                </div>

                <div class="mt-6 rounded-xl border border-amber-300/20 bg-amber-300/[.06] p-4 text-sm leading-6 text-amber-100">
                    This screen contains private account information. Use it only for authorized support or administration.
                    SmartCV records account feature use and administrator access from now on; older actions were not logged.
                    These logs are kept for 90 days and store page/action names, numeric record IDs, request type, and response result,
                    not form text, passwords, IP addresses, or uploaded file contents.
                </div>

                <section class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                    @foreach ([
                    ['Resumes', $user->resumes->count()],
                    ['Job applications', $user->jobApplications->count()],
                    ['Interview sessions', $user->interviewSessions->count()],
                    ['Skills', $user->skills->count()],
                    ['Career goals', $user->careerGoals->count()],
                    ['Portfolio projects', $user->portfolioProjects->count()],
                    ['AI analyses', $user->aiAnalyses->count()],
                    ['Cover letters', $user->coverLetters->count()],
                    ['Learning paths', $user->learningPaths->count()],
                    ['Saved job searches', $user->jobSearches->count()],
                    ['Vault documents', $user->privateDocuments->count()],
                    ['Notifications', $notifications->count()],
                    ['Support requests', $user->supportRequests->count()],
                    ] as [$label, $count])
                    <article class="card p-4">
                        <p class="text-xs text-zinc-400">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-semibold">{{ $count }}</p>
                    </article>
                    @endforeach
                </section>

                <section class="mt-5 grid gap-5 xl:grid-cols-2">
                    <article class="card p-5 sm:p-6">
                        <h2 class="font-semibold">Account and career profile</h2>
                        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                            <div>
                                <dt class="text-xs text-zinc-500">Target role</dt>
                                <dd class="mt-1">{{ $user->target_role ?: 'Not set' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500">Experience level</dt>
                                <dd class="mt-1">{{ $user->experience_level ?: 'Not set' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500">Onboarding completed</dt>
                                <dd class="mt-1">{{ $user->onboarding_completed_at?->format('M j, Y g:i A') ?? 'No' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500">Last account update</dt>
                                <dd class="mt-1">{{ $user->updated_at?->format('M j, Y g:i A') ?? 'Unknown' }}</dd>
                            </div>
                        </dl>
                        @if ($user->careerProfile)
                        <details class="mt-5 rounded-lg border border-white/[.08] p-3">
                            <summary class="cursor-pointer text-sm font-medium text-cyan-200">Show all career-profile fields</summary>
                            <dl class="mt-3 space-y-3">
                                @foreach ($user->careerProfile->getAttributes() as $field => $value)
                                <div class="border-t border-white/[.06] pt-2">
                                    <dt class="text-xs capitalize text-zinc-500">{{ str_replace('_', ' ', $field) }}</dt>
                                    <dd class="mt-1 whitespace-pre-wrap break-words text-sm text-zinc-300">{{ is_scalar($value) ? ($value === null || $value === '' ? '—' : $value) : json_encode($value) }}</dd>
                                </div>
                                @endforeach
                            </dl>
                        </details>
                        @else
                        <p class="mt-4 text-sm text-zinc-500">No separate career profile saved.</p>
                        @endif
                    </article>

                    <article class="card p-5 sm:p-6">
                        <h2 class="font-semibold">Account preferences</h2>
                        @if ($user->preferences)
                        <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($user->preferences->getAttributes() as $field => $value)
                            @continue(in_array($field, ['id', 'user_id', 'created_at', 'updated_at'], true))
                            <div class="rounded-lg border border-white/[.07] p-3">
                                <dt class="text-xs capitalize text-zinc-500">{{ str_replace('_', ' ', $field) }}</dt>
                                <dd class="mt-1 text-sm text-zinc-200">{{ is_numeric($value) && in_array($field, ['ai_processing_enabled', 'retain_ai_history', 'email_reminders', 'weekly_career_review', 'in_app_reminders'], true) ? ((bool) $value ? 'On' : 'Off') : ($value ?: '—') }}</dd>
                            </div>
                            @endforeach
                        </dl>
                        @else
                        <p class="mt-4 text-sm text-zinc-500">No saved preferences yet.</p>
                        @endif
                    </article>
                </section>

                <section class="mt-5 space-y-4">
                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Resume records ({{ $user->resumes->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->resumes as $resume)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <div class="flex flex-wrap justify-between gap-3">
                                    <div>
                                        <h3 class="font-medium">{{ $resume->name }}</h3>
                                        <p class="mt-1 text-xs text-zinc-500">{{ $resume->original_filename }} · {{ $resume->mime_type }} · {{ number_format($resume->file_size / 1024, 1) }} KB</p>
                                        <p class="mt-1 text-xs text-zinc-500">{{ ucfirst($resume->parse_status) }}{{ $resume->is_primary ? ' · Primary resume' : '' }}{{ $resume->trashed() ? ' · Deleted' : '' }}</p>
                                    </div>
                                    <span class="text-xs text-zinc-500">Added {{ $resume->created_at?->format('M j, Y') }}</span>
                                </div>
                                @if ($resume->extracted_text)
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show extracted resume text</summary>
                                    <pre class="mt-2 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-300">{{ $resume->extracted_text }}</pre>
                                </details>
                                @endif
                                @foreach ($resume->versions as $version)
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-violet-200">Version {{ $version->version_number }} · {{ $version->label }}{{ $version->is_current ? ' · Current' : '' }}</summary>
                                    <pre class="mt-2 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-300">{{ json_encode($version->content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                                @endforeach
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No resumes saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Job applications ({{ $user->jobApplications->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->jobApplications as $job)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $job->role }} · {{ $job->company }}</h3>
                                <p class="mt-1 text-xs capitalize text-zinc-500">{{ $job->status }}{{ $job->trashed() ? ' · Deleted' : '' }} · {{ $job->location ?: 'Location not set' }} · {{ $job->work_mode ?: 'Work mode not set' }}</p>
                                <p class="mt-2 text-xs text-zinc-500">Source: {{ $job->source ?: 'Manual entry' }} · Applied: {{ $job->applied_at?->format('M j, Y') ?? 'Not recorded' }} · Follow up: {{ $job->follow_up_at?->format('M j, Y') ?? 'None' }}</p>
                                @if ($job->job_url)<p class="mt-2 break-all text-xs text-cyan-200">{{ $job->job_url }}</p>@endif
                                @if ($job->notes)<p class="mt-2 whitespace-pre-wrap text-sm text-zinc-300">{{ $job->notes }}</p>@endif
                                @if ($job->contacts->isNotEmpty() || $job->attachments->isNotEmpty())
                                <div class="mt-3 space-y-2 text-xs text-zinc-400">
                                    @foreach ($job->contacts as $contact)
                                    <p>Contact: {{ $contact->name }}{{ $contact->role ? ' · ' . $contact->role : '' }}{{ $contact->email ? ' · ' . $contact->email : '' }}{{ $contact->notes ? ' · ' . $contact->notes : '' }}</p>
                                    @endforeach
                                    @foreach ($job->attachments as $attachment)
                                    <p>Attachment: {{ $attachment->original_filename }} · {{ $attachment->mime_type }} · {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                    @endforeach
                                </div>
                                @endif
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No job applications saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Interview sessions ({{ $user->interviewSessions->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->interviewSessions as $session)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $session->title }}</h3>
                                <p class="mt-1 text-xs capitalize text-zinc-500">{{ $session->target_role ?: 'General role' }} · {{ $session->status }} · {{ $session->overall_score !== null ? $session->overall_score . '/100' : 'Not scored' }}</p>
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show questions, answers, and feedback</summary>
                                    <pre class="mt-2 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-300">{{ json_encode(['questions' => $session->questions, 'responses' => $session->responses, 'feedback' => $session->feedback], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No interview sessions saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Skills and milestones ({{ $user->skills->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->skills as $skill)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $skill->name }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ $skill->category ?: 'Uncategorised' }} · {{ $skill->proficiency ?? 0 }}% proficiency · Target {{ $skill->target_proficiency ?? '—' }}% · {{ $skill->years_experience ?? '—' }} years</p>
                                @if ($skill->evidence)<p class="mt-2 whitespace-pre-wrap text-sm text-zinc-300">{{ $skill->evidence }}</p>@endif
                                @if ($skill->certificate_original_filename)<p class="mt-2 text-xs text-zinc-400">Certificate: {{ $skill->certificate_original_filename }}</p>@endif
                                @if ($skill->milestones->isNotEmpty())
                                <ul class="mt-3 space-y-1 text-xs text-zinc-400">
                                    @foreach ($skill->milestones as $milestone)
                                    <li>{{ $milestone->title }} · {{ str_replace('_', ' ', $milestone->status) }}</li>
                                    @endforeach
                                </ul>
                                @endif
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No skills saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Career goals and milestones ({{ $user->careerGoals->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->careerGoals as $goal)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $goal->title }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ $goal->target_role ?: 'No target role' }} · {{ $goal->progress ?? 0 }}% · {{ $goal->status }}</p>
                                @if ($goal->motivation)<p class="mt-2 text-sm text-zinc-300">Why: {{ $goal->motivation }}</p>@endif
                                @if ($goal->weekly_action)<p class="mt-2 text-sm text-zinc-300">Next action: {{ $goal->weekly_action }}</p>@endif
                                @if ($goal->milestones)
                                <pre class="mt-3 max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-400">{{ json_encode($goal->milestones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @endif
                                @if ($goal->career_advice)
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show saved AI career advice</summary>
                                    <pre class="mt-2 max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-400">{{ json_encode($goal->career_advice, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                                @endif
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No career goals saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Portfolio projects ({{ $user->portfolioProjects->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->portfolioProjects as $project)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $project->title }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ $project->role ?: 'Role not set' }} · {{ $project->status ?: 'Status not set' }}{{ $project->trashed() ? ' · Deleted' : '' }}</p>
                                @foreach (['description' => 'Description', 'outcome' => 'Outcome', 'case_study' => 'Case study'] as $field => $label)
                                @if ($project->{$field})<p class="mt-2 whitespace-pre-wrap text-sm text-zinc-300"><strong>{{ $label }}:</strong> {{ $project->{$field} }}</p>@endif
                                @endforeach
                                @if ($project->skills)<p class="mt-2 text-xs text-zinc-400">Skills: {{ is_array($project->skills) ? implode(', ', $project->skills) : $project->skills }}</p>@endif
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No portfolio projects saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">AI analysis records ({{ $user->aiAnalyses->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->aiAnalyses as $analysis)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium capitalize">{{ str_replace('_', ' ', $analysis->analysis_type) }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ ucfirst($analysis->status) }} · Score {{ $analysis->score ?? data_get($analysis->result, 'score', '—') }} · {{ $analysis->provider ?: 'Provider not recorded' }} · {{ $analysis->created_at?->format('M j, Y g:i A') }}</p>
                                @if ($analysis->error_message)<p class="mt-2 text-sm text-rose-200">{{ $analysis->error_message }}</p>@endif
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show saved result and input snapshot</summary>
                                    <pre class="mt-2 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-300">{{ json_encode(['result' => $analysis->result, 'input_snapshot' => $analysis->input_snapshot], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No AI analysis records saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Cover letters ({{ $user->coverLetters->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->coverLetters as $letter)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $letter->title }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ $letter->company_name ?: 'Company not set' }} · {{ $letter->status }}{{ $letter->trashed() ? ' · Deleted' : '' }}</p>
                                @if ($letter->subject)<p class="mt-2 text-sm text-zinc-300">Subject: {{ $letter->subject }}</p>@endif
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show private letter text</summary>
                                    <div class="mt-2 space-y-2 whitespace-pre-wrap rounded-lg bg-black/20 p-3 text-sm text-zinc-300">{{ $letter->opening }}{{ $letter->opening ? "\n\n" : '' }}{{ $letter->body }}{{ $letter->closing ? "\n\n" . $letter->closing : '' }}{{ $letter->signature_name ? "\n\n" . $letter->signature_name : '' }}</div>
                                </details>
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No cover letters saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Learning paths ({{ $user->learningPaths->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->learningPaths as $path)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $path->title }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ $path->target_role ?: 'Career growth' }} · {{ $path->items->count() }} steps</p>
                                <p class="mt-2 text-sm text-zinc-300">{{ $path->summary }}</p>
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show all learning steps</summary>
                                    <ol class="mt-2 list-decimal space-y-2 pl-5 text-sm text-zinc-300">@foreach ($path->items as $item)<li><strong>{{ $item->title }}</strong> · {{ $item->skill_name }} · {{ str_replace('_', ' ', $item->status) }}
                                            <p class="mt-1 text-xs text-zinc-400">{{ $item->description }}</p>
                                        </li>@endforeach</ol>
                                </details>
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No learning paths saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Private document vault ({{ $user->privateDocuments->count() }})</summary>
                        <p class="mt-2 text-xs leading-5 text-zinc-500">Original files are not opened or downloaded here. This list shows saved file details and any text SmartCV extracted.</p>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->privateDocuments as $document)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $document->name }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ $document->original_filename }} · {{ $document->mime_type }} · {{ number_format($document->file_size / 1024, 1) }} KB · {{ $document->category ?: 'Uncategorised' }}</p>
                                <p class="mt-1 text-xs text-zinc-500">Uploaded {{ $document->created_at?->format('M j, Y g:i A') }}</p>
                                @if ($document->extracted_text)<details class="mt-3">
                                    <summary class="cursor-pointer text-xs text-cyan-200">Show extracted text</summary>
                                    <pre class="mt-2 max-h-96 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-black/20 p-3 text-xs text-zinc-300">{{ $document->extracted_text }}</pre>
                                </details>@endif
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No private documents saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Saved job searches ({{ $user->jobSearches->count() }})</summary>
                        <ul class="mt-4 space-y-2 text-sm">
                            @forelse ($user->jobSearches as $search)
                            <li class="rounded-lg border border-white/[.08] p-3">{{ $search->queryText() ?: 'Empty search' }} · {{ $search->is_alert_enabled ? 'Alerts on' : 'Alerts off' }} · {{ $search->created_at?->format('M j, Y') }}</li>
                            @empty
                            <li class="text-zinc-500">No saved searches.</li>
                            @endforelse
                        </ul>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Support requests ({{ $user->supportRequests->count() }})</summary>
                        <div class="mt-4 space-y-3">
                            @forelse ($user->supportRequests as $ticket)
                            <article class="rounded-xl border border-white/[.08] p-4">
                                <h3 class="font-medium">{{ $ticket->subject }}</h3>
                                <p class="mt-1 text-xs text-zinc-500">{{ ucfirst($ticket->category) }} · {{ str_replace('_', ' ', $ticket->status) }} · {{ $ticket->created_at?->format('M j, Y g:i A') }}</p>
                                <p class="mt-2 whitespace-pre-wrap text-sm text-zinc-300">{{ $ticket->message }}</p>
                                @if ($ticket->admin_response)<p class="mt-2 whitespace-pre-wrap text-sm text-cyan-100">Admin reply: {{ $ticket->admin_response }}</p>@endif
                            </article>
                            @empty
                            <p class="text-sm text-zinc-500">No support requests saved.</p>
                            @endforelse
                        </div>
                    </details>

                    <details class="card p-5 sm:p-6">
                        <summary class="cursor-pointer font-semibold">Notifications ({{ $notifications->count() }})</summary>
                        <ul class="mt-4 space-y-3">
                            @forelse ($notifications as $notification)
                            <li class="rounded-lg border border-white/[.08] p-3">
                                <p class="font-medium">{{ data_get($notification->data, 'title', $notification->type) }}</p>
                                <p class="mt-1 text-sm text-zinc-400">{{ data_get($notification->data, 'body', 'No message stored.') }}</p>
                                <p class="mt-1 text-xs text-zinc-500">{{ $notification->created_at?->format('M j, Y g:i A') }} · {{ $notification->read_at ? 'Read' : 'Unread' }}</p>
                            </li>
                            @empty
                            <li class="text-sm text-zinc-500">No notifications saved.</li>
                            @endforelse
                        </ul>
                    </details>
                </section>

                <section class="mt-5 card p-5 sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <p class="eyebrow">Feature usage history</p>
                            <h2 class="mt-2 text-xl font-semibold">Recent account activity</h2>
                            <p class="mt-1 text-xs text-zinc-500">{{ $user->activities()->count() }} recorded actions · newest first</p>
                        </div>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <thead class="border-b border-white/[.08] text-xs uppercase tracking-wide text-zinc-500">
                                <tr>
                                    <th class="pb-2 font-medium">Time</th>
                                    <th class="pb-2 font-medium">Feature / page</th>
                                    <th class="pb-2 font-medium">Request</th>
                                    <th class="pb-2 font-medium">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activities as $activity)
                                <tr class="border-b border-white/[.06]">
                                    <td class="py-3 text-xs text-zinc-400">{{ $activity->created_at?->format('M j, Y g:i A') }}</td>
                                    <td class="py-3">{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $activity->route_name)) }}</td>
                                    <td class="py-3 text-xs text-zinc-400">{{ $activity->http_method }}</td>
                                    <td class="py-3 text-xs {{ $activity->response_code < 400 ? 'text-emerald-200' : 'text-rose-200' }}">
                                        {{ $activity->response_code }}
                                        @if ($activity->route_parameters)
                                        · record {{ implode(', ', $activity->route_parameters) }}
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-zinc-500">No activity recorded yet. New feature use will appear here.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($activities->hasPages())
                    <div class="mt-4">{{ $activities->links() }}</div>
                    @endif
                </section>

                <section class="mt-5 card p-5 sm:p-6">
                    <div>
                        <p class="eyebrow">Administrator audit</p>
                        <h2 class="mt-2 text-xl font-semibold">Admin access to this account</h2>
                        <p class="mt-1 text-xs text-zinc-500">{{ $adminActivities->total() }} recorded accesses or actions · kept for 90 days</p>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[620px] text-left text-sm">
                            <thead class="border-b border-white/[.08] text-xs uppercase tracking-wide text-zinc-500">
                                <tr>
                                    <th class="pb-2 font-medium">Time</th>
                                    <th class="pb-2 font-medium">Administrator</th>
                                    <th class="pb-2 font-medium">Action</th>
                                    <th class="pb-2 font-medium">Request / result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adminActivities as $activity)
                                <tr class="border-b border-white/[.06]">
                                    <td class="py-3 text-xs text-zinc-400">{{ $activity->created_at?->format('M j, Y g:i A') }}</td>
                                    <td class="py-3">{{ $activity->admin?->email ?? 'Administrator account deleted' }}</td>
                                    <td class="py-3">{{ \Illuminate\Support\Str::headline(str_replace('.', ' ', $activity->route_name)) }}</td>
                                    <td class="py-3 text-xs text-zinc-400">{{ $activity->http_method }} · {{ $activity->response_code }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-zinc-500">No administrator access recorded yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($adminActivities->hasPages())
                    <div class="mt-4">{{ $adminActivities->links() }}</div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</body>

</html>
