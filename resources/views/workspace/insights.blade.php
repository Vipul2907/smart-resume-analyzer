<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Career Insights · SmartCV</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
<div class="min-h-screen">
  <x-workspace-sidebar active-screen="insights" />
  <div class="min-h-screen lg:pl-64">
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
      <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a>
      <p class="hidden text-sm text-zinc-500 sm:block">Your private career direction</p>
      <a href="{{ route('profile') }}" class="flex items-center gap-2 text-sm font-medium"><span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-violet-400 to-cyan-300 text-slate-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>{{ auth()->user()->name }}</a>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
      <div class="flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div><p class="eyebrow">Career workspace</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Career insights and goals</h1><p class="mt-2 max-w-2xl text-sm text-zinc-400">Set a direction, break it into honest milestones, and ask AI for advice based on your own saved progress.</p></div>
        <a href="{{ route('learning-paths.index') }}" class="btn btn-secondary">Open learning paths</a>
      </div>

      @if(session('status'))<div class="mt-6 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-100">{{ session('status') }}</div>@endif
      @if($errors->any())<div class="mt-6 rounded-xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm text-rose-100"><p class="font-semibold">Please check the form.</p><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

      <section class="mt-7 grid gap-5 xl:grid-cols-[.68fr_1.32fr]">
        <aside class="card h-max p-5">
          <p class="eyebrow">New direction</p><h2 class="mt-2 font-semibold">Create a career goal</h2><p class="mt-2 text-xs leading-5 text-zinc-500">Use a real outcome, not only a job title. Example: validate a business idea or move into product design by next year.</p>
          <form method="POST" action="{{ route('goals.store') }}" class="mt-5 space-y-3">@csrf
            <label class="block text-xs text-zinc-400">Goal<input class="input mt-1" name="title" value="{{ old('title') }}" placeholder="Launch a validated local-business software idea" required></label>
            <label class="block text-xs text-zinc-400">Target role <span class="text-zinc-600">(optional)</span><input class="input mt-1" name="target_role" value="{{ old('target_role') }}" placeholder="Founder, product designer, analyst"></label>
            <label class="block text-xs text-zinc-400">Target industry <span class="text-zinc-600">(optional)</span><input class="input mt-1" name="target_industry" value="{{ old('target_industry') }}" placeholder="Retail technology, healthcare, education"></label>
            <div class="grid gap-3 sm:grid-cols-2"><label class="block text-xs text-zinc-400">Target date<input class="input mt-1" type="date" name="target_date" value="{{ old('target_date') }}"></label><label class="block text-xs text-zinc-400">Target yearly salary <span class="text-zinc-600">(optional)</span><input class="input mt-1" type="number" min="0" name="target_salary" value="{{ old('target_salary') }}" placeholder="e.g. 1200000"></label></div>
            <label class="block text-xs text-zinc-400">Starting progress<input class="input mt-1" type="number" min="0" max="100" name="progress" value="{{ old('progress', 0) }}"></label>
            <label class="block text-xs text-zinc-400">Why it matters<textarea class="input mt-1 h-24" name="motivation" placeholder="What would change for you if you achieved this?">{{ old('motivation') }}</textarea></label>
            <label class="block text-xs text-zinc-400">This week's action<textarea class="input mt-1 h-20" name="weekly_action" placeholder="One small, measurable action you will complete this week.">{{ old('weekly_action') }}</textarea></label>
            <button class="btn btn-primary w-full">Save career goal</button>
          </form>
        </aside>

        <div class="space-y-4">
          @forelse($goals as $goal)
            @php($advice = is_array($goal->career_advice) ? $goal->career_advice : [])
            @php($milestones = collect($goal->milestones ?? []))
            <article class="card p-5 sm:p-6">
              <div class="flex flex-col justify-between gap-4 sm:flex-row">
                <div><p class="eyebrow">{{ $goal->status === 'completed' ? 'Goal achieved' : 'Active career goal' }}</p><h2 class="mt-2 text-xl font-semibold">{{ $goal->title }}</h2><p class="mt-2 text-sm text-zinc-400">{{ $goal->target_role ?: 'Career direction' }}@if($goal->target_industry) · {{ $goal->target_industry }}@endif @if($goal->target_date) · target {{ $goal->target_date->format('M j, Y') }}@endif</p></div>
                <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-center"><strong class="block text-2xl text-cyan-200">{{ $goal->progress }}%</strong><span class="text-xs text-zinc-400">goal progress</span></div>
              </div>
              <progress class="mt-5 h-2 w-full overflow-hidden rounded-full accent-cyan-300" value="{{ $goal->progress }}" max="100">{{ $goal->progress }}%</progress>
              <div class="mt-4 grid gap-3 text-sm md:grid-cols-2">@if($goal->motivation)<div class="rounded-xl border border-white/[.07] p-4"><p class="text-xs font-semibold text-zinc-300">WHY THIS MATTERS</p><p class="mt-2 leading-6 text-zinc-400">{{ $goal->motivation }}</p></div>@endif @if($goal->weekly_action)<div class="rounded-xl border border-violet-400/20 bg-violet-400/[.06] p-4"><p class="text-xs font-semibold text-violet-200">THIS WEEK</p><p class="mt-2 leading-6 text-zinc-300">{{ $goal->weekly_action }}</p></div>@endif</div>

              <div class="mt-5 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end"><form method="POST" action="{{ route('goals.update', $goal) }}" class="grid gap-2 sm:grid-cols-[130px_1fr_auto]">@csrf @method('PATCH')<label class="text-xs text-zinc-400">Progress<input class="input mt-1" type="number" min="0" max="100" name="progress" value="{{ $goal->progress }}"></label><label class="text-xs text-zinc-400">Next weekly action<input class="input mt-1" name="weekly_action" value="{{ $goal->weekly_action }}" placeholder="One measurable action"></label><button class="btn btn-secondary self-end">Update</button></form><form method="POST" action="{{ route('goals.destroy', $goal) }}" onsubmit="return confirm('Remove this career goal and its milestones?')">@csrf @method('DELETE')<button class="btn border border-rose-400/25 text-rose-200">Remove goal</button></form></div>

              <div class="mt-6 border-t border-white/[.07] pt-5"><div class="flex flex-wrap items-center justify-between gap-2"><div><h3 class="font-semibold">Career milestones</h3><p class="mt-1 text-xs text-zinc-500">{{ $goal->milestone_summary['completed'] ?? 0 }} of {{ $goal->milestone_summary['total'] ?? 0 }} completed</p></div></div>
                <div class="mt-4 space-y-2">@forelse($milestones as $milestone)<div class="rounded-xl border border-white/[.07] p-3"><div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><p class="text-sm font-medium">{{ $milestone['title'] ?? 'Untitled milestone' }}</p><p class="mt-1 text-xs text-zinc-500">{{ !empty($milestone['target_date']) ? 'Target '.$milestone['target_date'] : 'No target date' }}</p></div><div class="flex flex-wrap items-center gap-2"><form method="POST" action="{{ route('goals.milestones.update', [$goal, $milestone['id'] ?? 'missing']) }}" class="flex items-center gap-2">@csrf @method('PATCH')<select class="rounded-lg border border-white/[.10] bg-white/[.04] px-2 py-2 text-xs" name="status"><option value="planned" @selected(($milestone['status'] ?? '') === 'planned')>Planned</option><option value="in_progress" @selected(($milestone['status'] ?? '') === 'in_progress')>In progress</option><option value="completed" @selected(($milestone['status'] ?? '') === 'completed')>Completed</option></select><button class="text-xs text-cyan-200">Save</button></form><form method="POST" action="{{ route('goals.milestones.destroy', [$goal, $milestone['id'] ?? 'missing']) }}" onsubmit="return confirm('Remove this milestone?')">@csrf @method('DELETE')<button class="text-xs text-rose-200">Remove</button></form></div></div></div>@empty<p class="rounded-xl border border-dashed border-white/[.12] p-4 text-sm text-zinc-500">No milestones yet. Add the first small proof that moves this goal forward.</p>@endforelse</div>
                <form method="POST" action="{{ route('goals.milestones.store', $goal) }}" class="mt-3 grid gap-2 sm:grid-cols-[1fr_170px_auto]">@csrf<label class="sr-only" for="milestone-{{ $goal->id }}">Milestone</label><input id="milestone-{{ $goal->id }}" class="input" name="title" placeholder="Add a measurable milestone" required><input class="input" type="date" name="target_date"><button class="btn btn-secondary">Add milestone</button></form>
              </div>

              <div class="mt-6 border-t border-white/[.07] pt-5"><div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"><div><h3 class="font-semibold">AI career recommendations</h3><p class="mt-1 text-xs text-zinc-500">Uses this goal first, then only your relevant saved skills, resumes, projects, and applications.</p></div><form method="POST" action="{{ route('goals.career-advice.store', $goal) }}">@csrf<button class="btn btn-primary">{{ $advice ? 'Refresh AI advice' : 'Get AI advice' }}</button></form></div>
                @if($advice)<div class="mt-4 rounded-xl border border-cyan-400/20 bg-cyan-400/[.05] p-4"><div class="flex flex-wrap items-center justify-between gap-3"><p class="text-sm leading-6 text-zinc-300">{{ $advice['summary'] ?? '' }}</p><span class="rounded-lg bg-cyan-400/10 px-3 py-2 text-sm text-cyan-200">Readiness {{ $advice['readiness_score'] ?? 0 }}/100</span></div><div class="mt-4 grid gap-4 md:grid-cols-3"><div><h4 class="text-xs font-semibold text-zinc-200">NEXT ACTIONS</h4><ul class="mt-2 space-y-2 text-xs leading-5 text-zinc-400">@forelse($advice['next_actions'] ?? [] as $item)<li>{{ $item }}</li>@empty<li>No action list was returned. Refresh the advice.</li>@endforelse</ul></div><div><h4 class="text-xs font-semibold text-zinc-200">GAPS TO ADDRESS</h4><ul class="mt-2 space-y-2 text-xs leading-5 text-zinc-400">@forelse($advice['gaps'] ?? [] as $item)<li>{{ $item }}</li>@empty<li>No gaps were identified yet.</li>@endforelse</ul></div><div><h4 class="text-xs font-semibold text-zinc-200">NEXT 7 DAYS</h4><ul class="mt-2 space-y-2 text-xs leading-5 text-zinc-400">@forelse($advice['weekly_plan'] ?? [] as $item)<li>{{ $item }}</li>@empty<li>Refresh the advice for a weekly plan.</li>@endforelse</ul></div></div></div>@else<div class="mt-4 rounded-xl border border-dashed border-white/[.12] p-4 text-sm text-zinc-500">Ask for advice after saving a goal. SmartCV does not use generic career tips or unrelated skills.</div>@endif
              </div>
            </article>
          @empty
            <div class="card border-dashed p-10 text-center text-sm text-zinc-400">No career goals saved yet. Create one clear direction and SmartCV can turn it into milestones and personalised next actions.</div>
          @endforelse
        </div>
      </section>
    </main>
  </div>
</div>
</body>
</html>
