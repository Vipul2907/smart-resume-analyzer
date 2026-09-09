<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Career analytics · SmartCV</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#070b18] text-zinc-100">
  <div class="min-h-screen">
    <x-workspace-sidebar active-screen="analytics" />
    <div class="min-h-screen lg:pl-64">
      <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
        <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a>
        <p class="hidden text-sm text-zinc-500 sm:block">Private career analytics</p>
        <a href="{{ route('profile') }}" class="flex items-center gap-2 text-sm font-medium"><span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-violet-400 to-cyan-300 text-slate-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>{{ auth()->user()->name }}</a>
      </header>
      <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
          <div>
            <p class="eyebrow">Reports and analytics</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Your career, measured clearly.</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">Every number on this page comes from your saved SmartCV data. Nothing is estimated and nothing is shared publicly.</p>
          </div>
          <div class="flex flex-wrap gap-2"><a class="btn btn-secondary" href="{{ route('analytics.print') }}" target="_blank">Print career report</a><a class="btn btn-secondary" href="{{ route('analytics.recruiter-report') }}" target="_blank">Recruiter-ready report</a><a class="btn btn-primary" href="{{ route('analytics.applications.export') }}">Export applications CSV</a></div>
        </div>

        <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          @foreach ([
          ['Applications', $metrics['applications'], 'Every saved opportunity'],
          ['Active pipeline', $metrics['active'], 'Saved, applied, interviewing, or offer'],
          ['Response rate', $metrics['response_rate'].'%', 'Interview or offer outcomes'],
          ['Offer rate', $metrics['offer_rate'].'%', 'Offers from all saved applications'],
          ['Interview score', $metrics['interview_score'] !== null ? $metrics['interview_score'].'/100' : '—', 'Completed practice rounds'],
          ['Skills tracked', $metrics['skills'], $metrics['skill_average'] !== null ? 'Average proficiency: '.$metrics['skill_average'].'%' : 'Add a proficiency to measure progress'],
          ['Goal progress', $metrics['goal_average'] !== null ? $metrics['goal_average'].'%' : '—', $metrics['goals'].' saved career goals'],
          ['Portfolio projects', $metrics['projects'], 'Private work samples'],
          ['Latest resume score', $metrics['resume_score'] !== null ? $metrics['resume_score'].'/100' : '—', 'Most recent completed AI analysis'],
          ] as [$label, $value, $hint])
          <article class="card p-5">
            <p class="text-sm text-zinc-400">{{ $label }}</p>
            <p class="mt-3 text-3xl font-semibold tracking-tight">{{ $value }}</p>
            <p class="mt-2 text-xs leading-5 text-zinc-500">{{ $hint }}</p>
          </article>
          @endforeach
        </section>

        <section class="mt-5 grid gap-5 xl:grid-cols-[1.25fr_.75fr]">
          <article class="card p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3">
              <div>
                <h2 class="font-semibold">Application activity</h2>
                <p class="mt-1 text-sm text-zinc-500">New tracker entries in the last six months.</p>
              </div><span class="text-xs text-zinc-600">Live data</span>
            </div>
            @php($maxApplications = max(1, $months->max('applications')))
            <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@foreach($months as $month)<div class="rounded-xl border border-white/[.08] p-3"><div class="flex items-center justify-between text-xs"><span class="text-zinc-400">{{ $month['label'] }}</span><span class="font-semibold text-zinc-200">{{ $month['applications'] }}</span></div>
                <progress class="mt-3 h-2 w-full overflow-hidden rounded-full accent-cyan-400" max="{{ $maxApplications }}" value="{{ $month['applications'] }}">{{ $month['applications'] }}</progress>
              </div>@endforeach</div>
            @if($metrics['applications'] === 0)<p class="mt-6 rounded-xl border border-dashed border-white/[.1] p-4 text-sm text-zinc-400">Start by saving an opening from <a class="text-cyan-300 underline" href="{{ route('discover') }}">Job discovery</a> or add an opportunity in the tracker. Your activity chart will grow from real entries.</p>@endif
          </article>
          <article class="card p-5 sm:p-6">
            <h2 class="font-semibold">Application funnel</h2>
            <p class="mt-1 text-sm text-zinc-500">Where your saved opportunities are today.</p>
            <div class="mt-6 space-y-4">@php($maxStatus = max(1, $statuses->max('count')))<div class="space-y-4">@foreach($statuses as $status)<div>
                  <div class="flex justify-between text-sm"><span class="text-zinc-300">{{ $status['label'] }}</span><span class="text-zinc-500">{{ $status['count'] }}</span></div>
                  <progress class="mt-2 h-2 w-full overflow-hidden rounded-full accent-violet-400" max="{{ $maxStatus }}" value="{{ $status['count'] }}">{{ $status['count'] }}</progress>
                </div>@endforeach</div>
            </div>
          </article>
        </section>

        <section class="mt-5 grid gap-5 xl:grid-cols-2">
          <article class="card p-5 sm:p-6">
            <div>
              <h2 class="font-semibold">Skill confidence</h2>
              <p class="mt-1 text-sm text-zinc-500">Your strongest saved skills and target gaps.</p>
            </div>
            <div class="mt-6 space-y-5">@forelse($topSkills as $skill)<div>
                <div class="flex items-center justify-between gap-3 text-sm"><span class="font-medium">{{ $skill['name'] }}</span><span class="text-zinc-400">{{ $skill['proficiency'] }}%{{ $skill['target'] ? ' / '.$skill['target'].'% target' : '' }}</span></div>
                <progress class="mt-2 h-2 w-full overflow-hidden rounded-full accent-cyan-400" max="100" value="{{ min(100, $skill['proficiency']) }}">{{ $skill['proficiency'] }}</progress>
              </div>@empty<p class="rounded-xl border border-dashed border-white/[.1] p-4 text-sm text-zinc-400">No skills measured yet. Visit <a class="text-cyan-300 underline" href="{{ route('skills') }}">Skill studio</a> and save a skill with a proficiency level.</p>@endforelse</div>
          </article>
          <article class="card p-5 sm:p-6">
            <div>
              <h2 class="font-semibold">Goal momentum</h2>
              <p class="mt-1 text-sm text-zinc-500">Progress you have recorded against your career plans.</p>
            </div>
            <div class="mt-6 space-y-4">@forelse($goals as $goal)<div class="rounded-xl border border-white/[.08] p-4">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <p class="font-medium">{{ $goal->title }}</p>
                    <p class="mt-1 text-xs capitalize text-zinc-500">{{ $goal->status }}{{ $goal->target_date ? ' · target '.$goal->target_date->format('M j, Y') : '' }}</p>
                  </div><span class="text-sm font-semibold text-cyan-200">{{ $goal->progress ?? 0 }}%</span>
                </div>
                <progress class="mt-3 h-2 w-full overflow-hidden rounded-full accent-violet-400" max="100" value="{{ min(100, (int) ($goal->progress ?? 0)) }}">{{ $goal->progress ?? 0 }}</progress>
              </div>@empty<p class="rounded-xl border border-dashed border-white/[.1] p-4 text-sm text-zinc-400">Create a goal in <a class="text-cyan-300 underline" href="{{ route('insights') }}">Career insights</a> to track its progress here.</p>@endforelse</div>
          </article>
        </section>

        <section class="mt-5 grid gap-5 xl:grid-cols-[.85fr_1.15fr]">
          <article class="card p-5 sm:p-6">
            <h2 class="font-semibold">Job sources</h2>
            <p class="mt-1 text-sm text-zinc-500">Which sources create real interview or offer movement.</p>
            <div class="mt-6 space-y-3">@forelse($sources as $source)<div class="rounded-xl border border-white/[.08] px-4 py-3">
                <div class="flex items-center justify-between gap-3"><span class="text-sm text-zinc-300">{{ $source['name'] }}</span><span class="text-xs text-cyan-200">{{ $source['rate'] }}% conversion</span></div>
                <p class="mt-1 text-xs text-zinc-500">{{ $source['converted'] }} interview/offer outcomes from {{ $source['total'] }} saved roles</p>
              </div>@empty<p class="rounded-xl border border-dashed border-white/[.1] p-4 text-sm text-zinc-400">Add opportunities to measure the sources that work best for you.</p>@endforelse</div>
          </article>
          <article class="card p-5 sm:p-6">
            <h2 class="font-semibold">Resume and ATS score history</h2>
            <p class="mt-1 text-sm text-zinc-500">Completed resume reviews, ATS foundations, and job matches.</p>
            <div class="mt-6 space-y-3">@forelse($analysisHistory as $analysis)<div class="flex items-center justify-between rounded-xl border border-white/[.08] px-4 py-3">
                <div>
                  <p class="text-sm font-medium capitalize">{{ $analysis['type'] }}</p>
                  <p class="mt-1 text-xs text-zinc-500">{{ $analysis['date'] }}</p>
                </div><span class="text-lg font-semibold text-cyan-200">{{ $analysis['score'] }}/100</span>
              </div>@empty<p class="rounded-xl border border-dashed border-white/[.1] p-4 text-sm text-zinc-400">Run a resume review or ATS check to build a private score history.</p>@endforelse</div>
          </article>
        </section>
        <section class="mt-5 card p-5 sm:p-6">
          <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
              <h2 class="font-semibold">Your data, your control</h2>
              <p class="mt-1 max-w-2xl text-sm leading-6 text-zinc-500">Download a private JSON copy of your saved SmartCV records. Uploaded files are not included; this keeps the export safe and compact.</p>
            </div><a class="btn btn-secondary shrink-0" href="{{ route('analytics.data.export') }}">Download my data</a>
          </div>
        </section>
        <p class="mt-6 text-xs text-zinc-600">Report generated {{ $generatedAt->format('M j, Y, g:i A') }} from your private SmartCV workspace.</p>
      </main>
    </div>
  </div>
</body>

</html>
