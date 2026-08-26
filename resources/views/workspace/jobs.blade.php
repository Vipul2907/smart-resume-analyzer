<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Job Tracker · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
@php
    $statuses = ['saved' => 'Saved', 'applied' => 'Applied', 'interviewing' => 'Interviewing', 'offer' => 'Offer', 'rejected' => 'Rejected', 'withdrawn' => 'Withdrawn'];
@endphp

<div class="min-h-screen">
    <x-workspace-sidebar active-screen="jobs" />
    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
            <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a>
            <p class="hidden text-sm text-zinc-500 sm:block">Your private application pipeline</p>
            <a href="{{ route('profile') }}" class="flex items-center gap-2 text-sm font-medium"><span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-violet-400 to-cyan-300 text-slate-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>{{ auth()->user()->name }}</a>
        </header>

        <main class="mx-auto max-w-[1700px] px-5 py-8 lg:px-9">
            <p class="eyebrow">Career workspace</p>
            <div class="mt-2 flex flex-wrap items-end justify-between gap-4"><div><h1 class="text-3xl font-semibold">Job tracker</h1><p class="mt-2 text-sm text-zinc-400">Track every real opportunity, follow-up, contact, and document.</p></div><a href="#new-job" class="btn btn-primary">Add opportunity</a></div>

            @if(session('status'))<div class="mt-6 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-100">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="mt-6 rounded-xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm text-rose-100">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif

            <section class="card mt-7 p-4"><form method="GET" class="flex flex-col gap-3 lg:flex-row"><input class="input flex-1" name="search" value="{{ request('search') }}" placeholder="Search company or role"><select class="input lg:w-52" name="status"><option value="">All statuses</option>@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected($activeFilter === $value)>{{ $label }} ({{ $jobCounts[$value] ?? 0 }})</option>@endforeach</select><button class="btn btn-secondary">Filter</button><a href="{{ route('jobs') }}" class="btn btn-secondary">Clear</a></form></section>

            <section id="new-job" class="card mt-5 p-5"><h2 class="font-semibold">Add a real opportunity</h2><form method="POST" action="{{ route('jobs.store') }}" class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">@csrf<label class="text-xs text-zinc-400">Company<input class="input mt-1" name="company" required></label><label class="text-xs text-zinc-400">Role<input class="input mt-1" name="role" required></label><label class="text-xs text-zinc-400">Status<select class="input mt-1" name="status">@foreach($statuses as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label><label class="text-xs text-zinc-400">Priority<select class="input mt-1" name="priority"><option value="0">Normal</option><option value="1">Important</option><option value="2">High</option><option value="3">Urgent</option></select></label><label class="text-xs text-zinc-400">Location<input class="input mt-1" name="location"></label><label class="text-xs text-zinc-400">Work mode<select class="input mt-1" name="work_mode"><option value="">Not specified</option><option>Remote</option><option>Hybrid</option><option>On-site</option></select></label><label class="text-xs text-zinc-400">Applied date<input class="input mt-1" type="date" name="applied_at"></label><label class="text-xs text-zinc-400">Follow-up reminder<input class="input mt-1" type="date" name="follow_up_at"></label><label class="text-xs text-zinc-400 md:col-span-2">Job link<input class="input mt-1" type="url" name="job_url" placeholder="https://"></label><label class="text-xs text-zinc-400 md:col-span-2">Notes<textarea class="input mt-1 h-20" name="notes"></textarea></label><button class="btn btn-primary md:col-span-2 xl:col-span-4">Save application</button></form></section>

            <section class="mt-5 grid gap-4 xl:grid-cols-4">
                @foreach(['saved', 'applied', 'interviewing', 'offer'] as $stage)
                    <div class="rounded-2xl border border-white/[.08] bg-white/[.02] p-3"><div class="mb-3 flex items-center justify-between px-1"><h2 class="font-semibold">{{ $statuses[$stage] }}</h2><span class="rounded-full bg-white/[.06] px-2 py-1 text-xs">{{ $jobs->where('status', $stage)->count() }}</span></div><div class="space-y-3">
                        @forelse($jobs->where('status', $stage) as $job)
                            <article class="card p-4"><div class="flex justify-between gap-3"><div><h3 class="font-semibold">{{ $job->role }}</h3><p class="mt-1 text-xs text-zinc-400">{{ $job->company }}{{ $job->location ? ' · '.$job->location : '' }}</p></div>@if($job->priority > 0)<span class="h-max rounded-full bg-amber-400/10 px-2 py-1 text-[10px] text-amber-200">P{{ $job->priority }}</span>@endif</div>@if($job->follow_up_at)<p class="mt-3 text-xs {{ $job->follow_up_at->isPast() ? 'text-rose-300' : 'text-cyan-200' }}">Follow up: {{ $job->follow_up_at->format('M j, Y') }}</p>@endif
                                <details class="mt-4"><summary class="cursor-pointer text-xs text-violet-300">Manage application</summary><form method="POST" action="{{ route('jobs.update', $job) }}" class="mt-3 space-y-2">@csrf @method('PATCH')<input class="input" name="company" value="{{ $job->company }}" required><input class="input" name="role" value="{{ $job->role }}" required><select class="input" name="status">@foreach($statuses as $value => $label)<option value="{{ $value }}" @selected($job->status === $value)>{{ $label }}</option>@endforeach</select><input class="input" name="location" value="{{ $job->location }}" placeholder="Location"><select class="input" name="work_mode"><option value="">Work mode</option>@foreach(['Remote', 'Hybrid', 'On-site'] as $mode)<option @selected(($job->work_mode ?? $job->work_type) === $mode)>{{ $mode }}</option>@endforeach</select><input class="input" type="url" name="job_url" value="{{ $job->job_url }}" placeholder="Job link"><input class="input" type="date" name="applied_at" value="{{ optional($job->applied_at ?? $job->application_date)->format('Y-m-d') }}"><input class="input" type="date" name="follow_up_at" value="{{ optional($job->follow_up_at)->format('Y-m-d') }}"><select class="input" name="priority">@foreach([0 => 'Normal', 1 => 'Important', 2 => 'High', 3 => 'Urgent'] as $priority => $label)<option value="{{ $priority }}" @selected($job->priority == $priority)>{{ $label }}</option>@endforeach</select><textarea class="input h-20" name="notes">{{ $job->notes }}</textarea><button class="btn btn-primary w-full">Save changes</button></form>
                                    <div class="mt-3 border-t border-white/[.07] pt-3"><p class="text-xs font-semibold">Recruiter contacts</p>@foreach($job->contacts as $contact)<div class="mt-2 flex justify-between gap-2 text-xs text-zinc-400"><span>{{ $contact->name }}{{ $contact->role ? ' · '.$contact->role : '' }}</span><form method="POST" action="{{ route('jobs.contacts.destroy', [$job, $contact]) }}">@csrf @method('DELETE')<button class="text-rose-200">Remove</button></form></div>@endforeach<form method="POST" action="{{ route('jobs.contacts.store', $job) }}" class="mt-2 space-y-2">@csrf<input class="input" name="name" placeholder="Contact name" required><input class="input" name="role" placeholder="Recruiter / hiring manager"><input class="input" type="email" name="email" placeholder="Email"><input class="input" type="url" name="linkedin_url" placeholder="LinkedIn URL"><button class="text-xs text-violet-300">Save contact</button></form></div>
                                    <div class="mt-3 border-t border-white/[.07] pt-3"><p class="text-xs font-semibold">Private attachments</p>@foreach($job->attachments as $attachment)<div class="mt-2 flex justify-between gap-2 text-xs"><a href="{{ route('jobs.attachments.download', [$job, $attachment]) }}" class="text-cyan-200">{{ $attachment->original_filename }}</a><form method="POST" action="{{ route('jobs.attachments.destroy', [$job, $attachment]) }}">@csrf @method('DELETE')<button class="text-rose-200">Remove</button></form></div>@endforeach<form method="POST" enctype="multipart/form-data" action="{{ route('jobs.attachments.store', $job) }}" class="mt-2 flex gap-2">@csrf<input class="input !py-2 text-xs" type="file" name="attachment" required><button class="btn btn-secondary">Upload</button></form></div><form method="POST" action="{{ route('jobs.destroy', $job) }}" class="mt-3" onsubmit="return confirm('Remove this job application?')">@csrf @method('DELETE')<button class="text-xs text-rose-200">Remove application</button></form></details>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-white/[.1] p-4 text-center text-xs text-zinc-600">No {{ $statuses[$stage] }} applications.</p>
                        @endforelse
                    </div></div>
                @endforeach
            </section>

            @if($activeFilter === '')<section class="mt-5"><h2 class="mb-3 font-semibold">Closed applications</h2><div class="grid gap-3 md:grid-cols-2">@forelse($jobs->whereIn('status', ['rejected', 'withdrawn']) as $job)<article class="card p-4"><span class="text-xs capitalize text-zinc-500">{{ $job->status }}</span><h3 class="mt-2 font-semibold">{{ $job->role }}</h3><p class="text-sm text-zinc-400">{{ $job->company }}</p></article>@empty<p class="text-sm text-zinc-500">No closed applications yet.</p>@endforelse</div></section>@endif
        </main>
    </div>
</div>
</body>
</html>
