@php
  $resumes = $resumes ?? auth()->user()->resumes()->orderByDesc('is_primary')->latest()->get();
  $selectedResume = $primaryResume ?? $resumes->firstWhere('is_primary', true) ?? $resumes->first();
  $jobs = auth()->user()->jobApplications()->latest()->get(['id', 'company', 'role']);
  $latestMatch = $selectedResume?->aiAnalyses()->where('analysis_type', 'job_match')->where('status', 'completed')->latest()->first();
  $result = $latestMatch?->result ?: [];
  $displayText = function (mixed $value) use (&$displayText): string {
      if (is_string($value) || is_numeric($value)) return trim((string) $value);
      if (is_array($value)) return collect($value)->map(fn ($nested) => $displayText($nested))->filter()->implode(' ');
      return '';
  };
@endphp

@if(! $selectedResume)
  <section class="card border-dashed p-10 text-center">
    <p class="eyebrow">Resume required</p>
    <h2 class="mt-3 text-xl font-semibold">Add a parsed resume before matching it to a role.</h2>
    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-zinc-500">SmartCV needs readable resume text to compare your experience with a job description.</p>
    <a href="{{ route('resumes') }}" class="btn btn-primary mt-5">Upload or create a resume</a>
  </section>
@else
  <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="space-y-5">
      <article class="card p-5 sm:p-6">
        <p class="eyebrow">Advanced AI · Step 4</p>
        <h2 class="mt-2 text-xl font-semibold">Match your resume to a job description.</h2>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">Get a practical fit score, missing skills, ATS keywords, resume improvements, interview questions, and a focused next-step plan.</p>

        <form method="GET" action="{{ route('match') }}" class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-end">
          <label class="block flex-1 text-xs text-zinc-400">Resume to compare
            <select name="resume" class="input mt-2">@foreach($resumes as $resume)<option value="{{ $resume->id }}" @selected($resume->id === $selectedResume->id)>{{ $resume->name }}{{ $resume->is_primary ? ' · Primary' : '' }}</option>@endforeach</select>
          </label>
          <button class="btn btn-secondary">Choose resume</button>
        </form>
      </article>

      <article class="card p-5 sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="font-semibold">Job description</h2><p class="mt-1 text-sm text-zinc-500">Selected resume: {{ $selectedResume->name }} · {{ strlen((string) $selectedResume->extracted_text) }} readable characters</p></div><span class="rounded-full bg-cyan-400/10 px-3 py-1 text-xs text-cyan-100">Private AI request</span></div>
        <form method="POST" action="{{ route('ai-matches.store', $selectedResume) }}" class="mt-6 space-y-4">@csrf
          <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-xs text-zinc-400">Target role<input class="input mt-2" name="target_role" value="{{ old('target_role') }}" placeholder="Product Designer"></label>
            <label class="block text-xs text-zinc-400">Related saved job<select class="input mt-2" name="job_application_id"><option value="">Not linked to a saved job</option>@foreach($jobs as $job)<option value="{{ $job->id }}" @selected(old('job_application_id') == $job->id)>{{ $job->company }} — {{ $job->role }}</option>@endforeach</select></label>
          </div>
          <label class="block text-xs text-zinc-400">Paste the full job description<textarea class="input mt-2 h-64 resize-y" name="job_description" required minlength="80" maxlength="12000" placeholder="Paste the responsibilities, required skills, qualifications, and preferred experience from the job post here.">{{ old('job_description') }}</textarea><span class="mt-2 block text-zinc-600">Minimum 80 characters. Do not include information you do not want processed by Groq.</span></label>
          <label class="flex items-start gap-3 rounded-xl border border-white/[.08] p-4 text-sm leading-6 text-zinc-300"><input type="checkbox" name="accepted_ai_privacy" value="1" class="mt-1 accent-cyan-400" required><span>I understand SmartCV will send this selected resume and job description to Groq, then save this private match result in my history.</span></label>
          <button class="btn btn-primary" @disabled(in_array($selectedResume->parse_status, ['empty', 'image_only'], true))>Run job match</button>
        </form>
      </article>

      <article class="card p-5 sm:p-6">
        <div class="flex items-center justify-between gap-4"><div><h2 class="font-semibold">Latest match result</h2><p class="mt-1 text-sm text-zinc-500">{{ $latestMatch?->completed_at?->diffForHumans() ?? 'Run your first comparison to see results.' }}</p></div>@if($latestMatch)<span class="rounded-xl bg-cyan-400/10 px-4 py-2 text-2xl font-semibold text-cyan-100">{{ $latestMatch->score ?? '--' }}<span class="text-sm text-cyan-100/60">/100</span></span>@endif</div>
        @if($latestMatch)
          @if($displayText($result['summary'] ?? null) !== '')<div class="mt-5 rounded-xl border border-cyan-400/15 bg-cyan-400/[.05] p-4 text-sm leading-6 text-zinc-300">{{ $displayText($result['summary']) }}</div>@endif
          <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach(['matching_skills' => 'Matching skills', 'missing_skills' => 'Skill gaps', 'keyword_suggestions' => 'ATS keywords to use naturally', 'resume_improvements' => 'Resume improvements', 'interview_questions' => 'Role-focused interview questions', 'next_actions' => 'Your next actions'] as $key => $label)
              @php($items = collect((array) ($result[$key] ?? []))->map($displayText)->filter())
              <div class="rounded-xl border border-white/[.08] p-4"><h3 class="text-sm font-semibold">{{ $label }}</h3><ul class="mt-3 space-y-2 text-sm leading-6 text-zinc-400">@forelse($items as $item)<li>{{ $item }}</li>@empty<li class="text-zinc-600">No suggestions returned for this section.</li>@endforelse</ul></div>
            @endforeach
          </div>
          @if($displayText($result['role_recommendation'] ?? null) !== '')<div class="mt-4 rounded-xl border border-violet-400/20 bg-violet-400/[.07] p-4"><p class="text-xs font-bold uppercase tracking-[.14em] text-violet-200">Role recommendation</p><p class="mt-2 text-sm leading-6 text-zinc-300">{{ $displayText($result['role_recommendation']) }}</p></div>@endif
        @else
          <div class="mt-5 rounded-xl border border-dashed border-white/[.12] p-6 text-sm leading-6 text-zinc-500">The result will show your strongest evidence for the role, the gaps to address, and what to improve before applying.</div>
        @endif
      </article>
    </div>

    <aside class="space-y-5">
      <article class="card p-5"><h2 class="font-semibold">What Step 4 gives you</h2><div class="mt-5 space-y-4 text-sm leading-6 text-zinc-400"><p><strong class="text-zinc-100">Job match:</strong> a clear resume-to-role fit score.</p><p><strong class="text-zinc-100">Skill gap:</strong> missing or weak requirements to learn or address.</p><p><strong class="text-zinc-100">Resume improvements:</strong> truthful ways to tailor your resume.</p><p><strong class="text-zinc-100">Interview prep:</strong> five questions based on this role.</p></div></article>
      <article class="card p-5"><h2 class="font-semibold">Recent matches</h2><div class="mt-4 divide-y divide-white/[.07]">@forelse($selectedResume->aiAnalyses()->where('analysis_type', 'job_match')->where('status', 'completed')->latest()->limit(6)->get() as $match)<div class="py-3"><div class="flex justify-between gap-3 text-sm"><span class="font-medium">{{ data_get($match->input_snapshot, 'target_role') ?: 'Job match' }}</span><span class="text-cyan-200">{{ $match->score ?? '--' }}/100</span></div><p class="mt-1 text-xs text-zinc-500">{{ $match->created_at->diffForHumans() }}</p></div>@empty<p class="text-sm text-zinc-500">No completed matches for this resume yet.</p>@endforelse</div></article>
    </aside>
  </section>
@endif
