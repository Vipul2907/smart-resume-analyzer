<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Live Job Board · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
<div class="min-h-screen">
    <x-workspace-sidebar active-screen="discover" />
    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
            <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a>
            <span class="hidden text-sm text-zinc-500 sm:block">Live technology roles and private saved searches</span>
            <a href="{{ route('profile') }}" class="text-sm font-medium">{{ auth()->user()->name }}</a>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
            <p class="eyebrow">Live opportunities</p>
            <div class="mt-2 flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
                <div>
                    <h1 class="text-3xl font-semibold tracking-tight">Job discovery</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">Browse live technology openings, save searches, compare a role with your resume, then apply on the employer’s original listing.</p>
                </div>
                <a href="{{ route('jobs') }}" class="btn btn-secondary">Open Job Tracker</a>
            </div>

            <section class="card mt-7 p-5">
                <form method="GET" action="{{ route('discover') }}" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_280px_auto] lg:items-end">
                    <label class="block text-xs text-zinc-400">Filter these live technology jobs
                        <input class="input mt-2" name="q" value="{{ $query }}" placeholder="Laravel, frontend, data analyst, designer...">
                    </label>
                    <label class="block text-xs text-zinc-400">Resume to analyze
                        <select class="input mt-2" name="resume">
                            @forelse($resumes as $resume)
                                <option value="{{ $resume->id }}" @selected($selectedResume?->id === $resume->id)>{{ $resume->name }}{{ $resume->is_primary ? ' · Primary' : '' }}</option>
                            @empty
                                <option value="">No resume available</option>
                            @endforelse
                        </select>
                    </label>
                    <button class="btn btn-primary">Refresh jobs</button>
                </form>
                <details class="mt-5 border-t border-white/[.08] pt-5">
                    <summary class="cursor-pointer text-sm font-semibold text-zinc-200">Save this search as a private alert</summary>
                    <form method="POST" action="{{ route('discover.searches.store') }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                        @csrf
                        <label class="text-xs text-zinc-400">Alert name<input class="input mt-2" name="name" value="{{ old('name', $query ? $query.' opportunities' : '') }}" placeholder="Remote Laravel roles" required></label>
                        <label class="text-xs text-zinc-400">Keywords<input class="input mt-2" name="keywords" value="{{ old('keywords', $query) }}" placeholder="Laravel developer" required></label>
                        <label class="text-xs text-zinc-400">Review frequency<select class="input mt-2" name="frequency"><option value="daily">Daily</option><option value="weekly" selected>Weekly</option></select></label>
                        <div class="flex flex-col justify-end gap-3"><label class="flex gap-2 text-xs text-zinc-300"><input type="checkbox" name="is_alert_enabled" value="1" checked> Keep alert active</label><button class="btn btn-secondary">Save job alert</button></div>
                    </form>
                </details>
            </section>

            @if($error)
                <div class="mt-6 rounded-xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm text-rose-100">{{ $error }}</div>
            @endif

            @if(! $selectedResume)
                <section class="card mt-6 border-dashed p-6 text-center">
                    <h2 class="text-lg font-semibold">Add a parsed resume to analyze a role</h2>
                    <p class="mt-2 text-sm text-zinc-400">You can still browse jobs now. Upload or create a resume to unlock private job matching with Groq.</p>
                    <a href="{{ route('resumes') }}" class="btn btn-primary mt-5">Add a resume</a>
                </section>
            @elseif(in_array($selectedResume->parse_status, ['empty', 'image_only'], true))
                <div class="mt-6 rounded-xl border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-100">“{{ $selectedResume->name }}” needs readable text before SmartCV can compare it with a job. Parse or replace this resume first.</div>
            @endif

            <div class="mt-7 flex items-center justify-between gap-3">
                <div><h2 class="text-xl font-semibold">Technology openings</h2><p class="mt-1 text-sm text-zinc-500">{{ $jobs->count() }} matching live openings on this page · Source: Arbeitnow</p></div>
                @if(data_get($meta, 'next_page'))
                    <a class="btn btn-secondary" href="{{ route('discover', ['q' => $query, 'resume' => $selectedResume?->id, 'page' => $page + 1]) }}">Next page</a>
                @endif
            </div>

            <section class="mt-5 grid gap-5 xl:grid-cols-2">
                @forelse($jobs as $job)
                    <article class="card card-hover flex flex-col p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $job['title'] }}</h3>
                                <p class="mt-1 text-sm text-zinc-400">{{ $job['company'] }}</p>
                            </div>
                            @if($job['remote'])<span class="rounded-full bg-cyan-400/10 px-3 py-1 text-xs font-semibold text-cyan-100">Remote</span>@endif
                        </div>
                        <p class="mt-4 text-sm text-zinc-400">{{ $job['location'] ?: ($job['remote'] ? 'Remote / location not specified' : 'Location not specified') }}</p>
                        @if($job['tags'])
                            <div class="mt-4 flex flex-wrap gap-2">@foreach($job['tags'] as $tag)<span class="rounded-full border border-violet-400/20 bg-violet-400/[.08] px-2.5 py-1 text-xs text-violet-100">{{ $tag }}</span>@endforeach</div>
                        @endif
                        <p class="mt-5 line-clamp-4 text-sm leading-6 text-zinc-400">{{ Str::limit($job['description'], 440) }}</p>
                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            @if(filter_var($job['url'], FILTER_VALIDATE_URL))
                                <a href="{{ $job['url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary">View & apply</a>
                            @else
                                <span class="btn btn-secondary cursor-not-allowed opacity-50">Listing unavailable</span>
                            @endif
                            <form method="POST" action="{{ route('discover.tracker.store') }}">
                                @csrf
                                <input type="hidden" name="company" value="{{ $job['company'] }}">
                                <input type="hidden" name="role" value="{{ $job['title'] }}">
                                <input type="hidden" name="location" value="{{ $job['location'] }}">
                                <input type="hidden" name="job_url" value="{{ $job['url'] }}">
                                <button class="btn btn-secondary w-full">Save to tracker</button>
                            </form>
                            @if($selectedResume && strlen($job['description']) >= 80 && !in_array($selectedResume->parse_status, ['empty', 'image_only'], true))
                                <form method="POST" action="{{ route('ai-matches.store', $selectedResume) }}">
                                    @csrf
                                    <input type="hidden" name="target_role" value="{{ $job['title'] }}">
                                    <input type="hidden" name="job_description" value="{{ $job['description'] }}">
                                    <input type="hidden" name="accepted_ai_privacy" value="1">
                                    <button class="btn btn-primary w-full">Analyze my resume for this role</button>
                                </form>
                            @else
                                <span class="btn btn-primary cursor-not-allowed opacity-50">Resume analysis unavailable</span>
                            @endif
                        </div>
                        <p class="mt-3 text-xs leading-5 text-zinc-600">Analysis uses the selected resume and this listing’s cleaned job description. Your resume and role details are sent to Groq only for this private match.</p>
                    </article>
                @empty
                    <article class="card col-span-full border-dashed p-10 text-center">
                        <h3 class="font-semibold">No technology roles matched this page</h3>
                        <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-zinc-500">Try a different filter or refresh the board. Arbeitnow’s listings change throughout the day.</p>
                    </article>
                @endforelse
            </section>

            <section class="card mt-7 p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="eyebrow">Saved searches</p><h2 class="mt-2 text-lg font-semibold">Your private job alerts</h2></div><span class="text-sm text-zinc-500">{{ $searches->count() }} saved</span></div>
                <div class="mt-5 grid gap-3 lg:grid-cols-2">
                    @forelse($searches as $search)
                        <article class="rounded-xl border border-white/[.08] p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="font-semibold">{{ $search->name }}</h3><p class="mt-1 text-sm text-zinc-400">{{ $search->queryText() }}</p><p class="mt-1 text-xs text-zinc-500">{{ $search->is_alert_enabled ? 'Active alert' : 'Alert paused' }} · review {{ $search->frequency }}</p></div><a href="{{ route('discover.searches.open', $search) }}" class="btn btn-secondary">Open jobs</a></div>
                            <div class="mt-4 flex flex-wrap gap-4"><form method="POST" action="{{ route('discover.searches.update', $search) }}" class="flex items-center gap-2">@csrf @method('PATCH')<select class="rounded-lg border border-white/[.1] bg-white/[.04] px-2 py-2 text-xs" name="frequency"><option value="daily" @selected($search->frequency === 'daily')>Daily</option><option value="weekly" @selected($search->frequency === 'weekly')>Weekly</option></select><label class="flex gap-1 text-xs text-zinc-300"><input type="checkbox" name="is_alert_enabled" value="1" @checked($search->is_alert_enabled)> Active</label><button class="text-xs text-cyan-200">Save</button></form><form method="POST" action="{{ route('discover.searches.destroy', $search) }}" onsubmit="return confirm('Remove this saved job alert?')">@csrf @method('DELETE')<button class="text-xs text-rose-200">Remove</button></form></div>
                        </article>
                    @empty
                        <p class="rounded-xl border border-dashed border-white/[.12] p-5 text-sm text-zinc-500 lg:col-span-2">No saved job alerts yet. Search for a role above, then save it for later.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
