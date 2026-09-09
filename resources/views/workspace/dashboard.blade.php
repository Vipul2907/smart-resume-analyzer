<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Overview · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
<div class="min-h-screen">
    <x-workspace-sidebar active-screen="dashboard" />
    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
            <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-500">CV</span></a>
            <p class="hidden text-sm text-zinc-500 sm:block">Your private career workspace</p>
            <a href="{{ route('profile') }}" class="text-sm font-semibold">{{ auth()->user()->name }}</a>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
            <section class="dashboard-hero overflow-hidden rounded-3xl border p-6 sm:p-8">
                <div class="relative z-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-end">
                    <div>
                        <p class="eyebrow">Today in SmartCV</p>
                        <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Make your next career move clear.</h1>
                        <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">{{ $activeGoal ? 'Your current focus: '.$activeGoal->title : 'Start with one small action: create a resume, save a job, or choose a career goal.' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('resumes.builder.create') }}" class="btn btn-primary">Create resume</a>
                        <a href="{{ route('discover') }}" class="btn btn-secondary">Find jobs</a>
                    </div>
                </div>
            </section>

            <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Career summary">
                @foreach([
                    ['Resume readiness', $resumeReadiness.'%', 'resumes'],
                    ['Active applications', $dashboardStats['activeApplications'], 'jobs'],
                    ['Completed practice', $dashboardStats['completedInterviews'], 'interviews'],
                    ['Skills tracked', $dashboardStats['skills'], 'skills'],
                ] as [$label, $value, $route])
                    <a href="/{{ $route }}" class="card card-hover p-5">
                        <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
                        <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
                        <span class="mt-3 inline-block text-xs font-semibold text-violet-600">Open tool →</span>
                    </a>
                @endforeach
            </section>

            <section class="mt-6 grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
                <div class="space-y-6">
                    <article class="card p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div><p class="eyebrow">Your next best action</p><h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $primaryResume ? ($primaryResume->last_analyzed_at ? 'Use your resume on a live opportunity.' : 'Analyse your resume before you apply.') : 'Build your first resume foundation.' }}</h2></div>
                            @if($primaryResume)
                                <a href="{{ route('analyze', ['resume' => $primaryResume->id]) }}" class="btn btn-primary">{{ $primaryResume->last_analyzed_at ? 'Open analysis' : 'Analyse resume' }}</a>
                            @else
                                <a href="{{ route('resumes.builder.create') }}" class="btn btn-primary">Create resume</a>
                            @endif
                        </div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <a href="{{ route('jobs') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Apply</span><strong class="mt-2 block text-sm text-slate-900">Add an application</strong><span class="mt-1 block text-xs leading-5 text-slate-500">Track a role, follow-up, contact, and attachment.</span></a>
                            <a href="{{ route('interviews') }}" class="rounded-2xl border border-slate-200 bg-slate-50 p-4"><span class="text-xs font-bold uppercase tracking-wider text-cyan-700">Practise</span><strong class="mt-2 block text-sm text-slate-900">Prepare an interview</strong><span class="mt-1 block text-xs leading-5 text-slate-500">Answer focused questions and save your progress.</span></a>
                        </div>
                    </article>

                    <article class="card p-6">
                        <div class="flex items-center justify-between gap-3"><div><p class="eyebrow">Application pipeline</p><h2 class="mt-2 text-xl font-semibold text-slate-900">Real opportunities, clearly organised.</h2></div><a href="{{ route('jobs') }}" class="text-sm font-semibold text-violet-600">Open tracker →</a></div>
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @forelse($recentJobs as $job)
                                <a href="{{ route('jobs') }}" class="rounded-xl border border-slate-200 p-4 transition hover:border-violet-300"><div class="flex justify-between gap-3"><strong class="text-sm text-slate-900">{{ $job->role }}</strong><span class="rounded-full bg-indigo-50 px-2 py-1 text-[10px] font-bold capitalize text-indigo-700">{{ $job->status }}</span></div><p class="mt-2 text-xs text-slate-500">{{ $job->company }}{{ $job->location ? ' · '.$job->location : '' }}</p></a>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-300 p-5 text-sm leading-6 text-slate-500 sm:col-span-2">No applications yet. Use the Live Job Board to discover a role, then save it to your private tracker.</div>
                            @endforelse
                        </div>
                    </article>
                </div>

                <div class="space-y-6">
                    <article class="card p-6"><p class="eyebrow">Upcoming</p><h2 class="mt-2 text-xl font-semibold text-slate-900">Follow-ups and practice</h2><div class="mt-5 space-y-3">@forelse($upcomingFollowUps as $job)<a href="{{ route('jobs') }}" class="block rounded-xl border border-slate-200 p-3"><strong class="block text-sm text-slate-900">{{ $job->company }}</strong><span class="mt-1 block text-xs text-slate-500">Follow up {{ $job->follow_up_at->format('M j') }} · {{ $job->role }}</span></a>@empty<p class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-500">No follow-ups are scheduled. Add one to an application when you need a reminder.</p>@endforelse</div><a href="{{ route('interviews') }}" class="mt-4 inline-block text-sm font-semibold text-violet-600">Plan interview practice →</a></article>
                    <article class="card p-6"><p class="eyebrow">Skill growth</p><h2 class="mt-2 text-xl font-semibold text-slate-900">What you are building</h2><div class="mt-5 space-y-4">@forelse($prioritySkills as $skill)<a href="{{ route('skills') }}" class="block"><div class="flex justify-between text-sm"><span class="font-semibold text-slate-800">{{ $skill->name }}</span><span class="text-slate-500">{{ $skill->proficiency ?? 0 }}%</span></div><div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gradient-to-r from-violet-500 to-cyan-400" style="width: {{ min(100, (int) ($skill->proficiency ?? 0)) }}%"></div></div></a>@empty<p class="rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-500">Add skills and evidence to see your professional growth here.</p>@endforelse</div></article>
                    <article class="card p-6"><p class="eyebrow">Recent AI work</p><div class="mt-4 space-y-3">@forelse($recentAnalyses as $analysis)<a href="{{ route('analyze') }}" class="flex items-center justify-between gap-3 text-sm"><span class="font-medium text-slate-800">{{ str_replace('_', ' ', ucfirst($analysis->analysis_type)) }}</span><span class="text-xs text-slate-500">{{ $analysis->created_at->diffForHumans() }}</span></a>@empty<p class="text-sm leading-6 text-slate-500">When you run an analysis, its private result history will appear here.</p>@endforelse</div></article>
                </div>
            </section>
        </main>
    </div>
</div>
</body>
</html>
