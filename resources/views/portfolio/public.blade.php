<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $profile->user->name }} · Portfolio</title>
  @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
  <main class="mx-auto max-w-6xl px-5 py-10 sm:px-8">
    <header class="flex items-center justify-between border-b border-white/[.08] pb-6">
      <a href="{{ route('home') }}" class="font-bold">SMART<span class="text-violet-300">CV</span></a>
      <span class="text-xs text-zinc-500">Professional portfolio</span>
    </header>

    <section class="grid gap-8 py-14 lg:grid-cols-[1.25fr_.75fr]">
      <div>
        <p class="eyebrow">{{ $profile->headline ?: 'Professional portfolio' }}</p>
        <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">{{ $profile->user->name }}</h1>
        @if($profile->location)
          <p class="mt-4 text-sm text-zinc-400">{{ $profile->location }}</p>
        @endif
        @if($profile->about)
          <p class="mt-6 max-w-2xl text-base leading-7 text-zinc-300">{{ $profile->about }}</p>
        @endif
        <div class="mt-7 flex flex-wrap gap-3">
          @if($profile->show_contact_email && $profile->contact_email)
            <a class="btn btn-primary" href="mailto:{{ $profile->contact_email }}">Contact {{ $profile->user->name }}</a>
          @endif
          @if($resume)
            <a class="btn btn-secondary" href="{{ route('portfolio.public.resume', $profile->public_slug) }}">Download resume</a>
          @endif
          @if($profile->linkedin_url)
            <a class="btn btn-secondary" href="{{ $profile->linkedin_url }}" target="_blank" rel="noreferrer">LinkedIn</a>
          @endif
          @if($profile->website_url)
            <a class="btn btn-secondary" href="{{ $profile->website_url }}" target="_blank" rel="noreferrer">Website</a>
          @endif
        </div>
      </div>
      <aside class="card h-max p-5"><p class="eyebrow">Availability</p><p class="mt-3 text-sm leading-6 text-zinc-300">{{ $profile->available_for_work ? 'Open to relevant opportunities and conversations.' : 'Portfolio shared for professional reference.' }}</p><p class="mt-4 text-xs text-zinc-500">This page shows only information the owner chose to share publicly.</p></aside>
    </section>

    <section class="border-t border-white/[.08] py-10">
      <div class="flex items-end justify-between gap-4"><div><p class="eyebrow">Selected work</p><h2 class="mt-2 text-2xl font-semibold">Projects and case studies</h2></div><span class="text-sm text-zinc-500">{{ $projects->count() }} public projects</span></div>
      <div class="mt-7 grid gap-5 md:grid-cols-2">
        @forelse($projects as $project)
          <article class="card overflow-hidden">
            @if($project->image_path)
              <img class="h-48 w-full object-cover" src="{{ route('portfolio.public.image', [$profile->public_slug, $project]) }}" alt="{{ $project->title }} project image">
            @endif
            <div class="p-5">
              <div class="flex justify-between gap-3"><div><h3 class="text-lg font-semibold">{{ $project->title }}</h3><p class="mt-1 text-xs text-cyan-200">{{ $project->role ?: 'Project' }}</p></div>@if($project->is_featured)<span class="rounded-full bg-violet-400/15 px-2 py-1 text-[10px] text-violet-200">FEATURED</span>@endif</div>
              <p class="mt-4 text-sm leading-6 text-zinc-400">{{ $project->description ?: $project->tagline }}</p>
              @if($project->outcome)
                <p class="mt-4 rounded-lg border border-emerald-400/15 bg-emerald-400/[.05] p-3 text-xs text-zinc-300"><strong class="text-emerald-200">Result:</strong> {{ $project->outcome }}</p>
              @endif
              @if($project->case_study)
                <details class="mt-4"><summary class="cursor-pointer text-sm text-violet-300">Read case study</summary><p class="mt-3 whitespace-pre-line text-sm leading-6 text-zinc-400">{{ $project->case_study }}</p></details>
              @endif
              @if($project->skills)
                <div class="mt-4 flex flex-wrap gap-2">@foreach($project->skills as $skill)<span class="rounded-full bg-white/[.06] px-2 py-1 text-xs text-zinc-300">{{ $skill }}</span>@endforeach</div>
              @endif
              <div class="mt-5 flex gap-3 text-sm">
                @if($project->project_url)<a class="text-cyan-200" href="{{ $project->project_url }}" target="_blank" rel="noreferrer">View project</a>@endif
                @if($project->repository_url)<a class="text-cyan-200" href="{{ $project->repository_url }}" target="_blank" rel="noreferrer">View code</a>@endif
              </div>
            </div>
          </article>
        @empty
          <div class="card border-dashed p-10 text-center text-sm text-zinc-400 md:col-span-2">No public projects have been published yet.</div>
        @endforelse
      </div>
    </section>
    <footer class="border-t border-white/[.08] py-6 text-center text-xs text-zinc-600">Built with SmartCV · This portfolio is managed by its owner.</footer>
  </main>
</body>
</html>
