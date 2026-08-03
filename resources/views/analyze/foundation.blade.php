@php
  $primaryResume = $primaryResume ?? auth()->user()->resumes()->where('is_primary', true)->first() ?: auth()->user()->resumes()->latest()->first();
  $latestAnalysis = $latestAnalysis ?? $primaryResume?->aiAnalyses()->latest()->first();
  $result = $latestAnalysis?->result ?: [];
  $analysisType = $screen === 'ats' ? 'ats_foundation' : 'resume_review';
@endphp

<section class="grid gap-4 xl:grid-cols-[1fr_.58fr]">
  <div class="space-y-4">
    @if(! $primaryResume)
      <article class="card border-dashed p-8 text-center">
        <p class="eyebrow">No resume selected</p>
        <h2 class="mt-3 text-xl font-semibold">Upload a resume first.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500">AI analysis starts after SmartCV has a parsed resume to review.</p>
        <a href="/resumes#upload-resume" class="btn btn-primary mt-5">Upload resume</a>
      </article>
    @else
      <article class="card p-5 sm:p-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
          <div>
            <p class="text-xs text-zinc-500">Selected resume</p>
            <h2 class="mt-1 text-xl font-semibold">{{ $primaryResume->name }}</h2>
            <p class="mt-1 text-sm text-zinc-500">{{ ucfirst(str_replace('_', ' ', $primaryResume->parse_status)) }} · {{ strlen((string) $primaryResume->extracted_text) }} readable characters</p>
          </div>
          <a href="/resumes/{{ $primaryResume->id }}" class="btn btn-secondary">Change resume</a>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border border-cyan-300/20 bg-cyan-400/[.08] p-4">
            <p class="text-xs text-cyan-100">{{ $screen === 'ats' ? 'ATS foundation' : 'Latest AI score' }}</p>
            <p class="mt-2 text-4xl font-semibold">{{ $latestAnalysis?->score ?? '--' }}<span class="text-base text-zinc-500">/100</span></p>
            <p class="mt-3 text-xs text-zinc-500">{{ $latestAnalysis ? $latestAnalysis->status.' · '.$latestAnalysis->created_at->diffForHumans() : 'No AI run yet' }}</p>
          </div>
          <div class="rounded-xl border border-white/[.08] p-4">
            <p class="text-xs text-zinc-500">Provider</p>
            <p class="mt-3 text-lg font-semibold text-zinc-100">Groq</p>
            <p class="mt-3 text-xs text-zinc-500">{{ config('services.groq.model') }}</p>
          </div>
          <div class="rounded-xl border border-white/[.08] p-4">
            <p class="text-xs text-zinc-500">Monthly free limit</p>
            <p class="mt-3 text-lg font-semibold text-zinc-100">{{ config('services.groq.free_monthly_limit', 10) }} runs</p>
            <p class="mt-3 text-xs text-zinc-500">Route and account limits are active.</p>
          </div>
        </div>
      </article>

      <article class="card p-5 sm:p-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
          <div>
            <h2 class="font-semibold">AI request</h2>
            <p class="mt-1 text-xs text-zinc-500">Your API key stays server-side. Resume text is sent only after consent.</p>
          </div>
          @if($latestAnalysis?->status === 'failed')<span class="rounded-full bg-rose-400/10 px-2 py-1 text-[10px] text-rose-200">RETRY READY</span>@endif
          @if($latestAnalysis?->status === 'completed')<span class="rounded-full bg-emerald-400/10 px-2 py-1 text-[10px] text-emerald-200">SUCCESS</span>@endif
          @if($latestAnalysis?->status === 'processing')<span class="rounded-full bg-amber-400/10 px-2 py-1 text-[10px] text-amber-200">LOADING</span>@endif
        </div>
        <form method="POST" action="{{ route('ai-analyses.store', $primaryResume) }}" class="mt-5 space-y-4" data-ai-form>
          @csrf
          <input type="hidden" name="analysis_type" value="{{ $analysisType }}">
          <label class="flex items-start gap-3 rounded-xl border border-white/[.08] p-4 text-sm leading-6 text-zinc-300">
            <input type="checkbox" name="accepted_ai_privacy" value="1" class="mt-1 accent-cyan-400" required>
            <span>I understand SmartCV will send this resume text to Groq for this analysis and save the result in my private history.</span>
          </label>
          <button class="btn btn-primary" @disabled($primaryResume->parse_status === 'image_only' || $primaryResume->parse_status === 'empty')>{{ $latestAnalysis?->status === 'failed' ? 'Retry analysis' : 'Run AI analysis' }}</button>
        </form>
        @if($latestAnalysis?->status === 'failed')
          <p class="mt-4 rounded-xl border border-rose-400/20 bg-rose-400/10 p-3 text-sm text-rose-100">{{ $latestAnalysis->error_message }}</p>
        @endif
      </article>

      <article class="card p-5 sm:p-6">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold">Latest result</h2>
          <span class="text-xs text-zinc-500">{{ $latestAnalysis?->completed_at?->diffForHumans() ?? 'Waiting for first run' }}</span>
        </div>
        @if($latestAnalysis?->status === 'completed')
          <div class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach(['strengths' => 'Strengths', 'weaknesses' => 'Weaknesses', 'missing_sections' => 'Missing sections', 'next_actions' => 'Next actions'] as $key => $label)
              <div class="rounded-xl border border-white/[.08] p-4">
                <h3 class="text-sm font-semibold">{{ $label }}</h3>
                <ul class="mt-3 space-y-2 text-xs leading-5 text-zinc-500">
                  @forelse((array) ($result[$key] ?? []) as $item)<li>{{ $item }}</li>@empty<li>No items returned yet.</li>@endforelse
                </ul>
              </div>
            @endforeach
          </div>
        @else
          <div class="mt-5 rounded-xl border border-white/[.08] p-5 text-sm leading-6 text-zinc-500">Run an analysis to see saved strengths, weaknesses, missing sections, and next actions here.</div>
        @endif
      </article>
    @endif
  </div>

  <aside class="space-y-4">
    <div class="card p-5">
      <h2 class="font-semibold">AI safety checks</h2>
      <div class="mt-5 space-y-3 text-sm text-zinc-400">
        <p>Server-side Groq config keeps the API key out of frontend code.</p>
        <p>Every request requires consent and a parsed resume owned by the signed-in user.</p>
        <p>Route throttling plus monthly free usage limits reduce abuse.</p>
      </div>
    </div>
    <div class="card p-5">
      <h2 class="font-semibold">Analysis history</h2>
      <div class="mt-4 divide-y divide-white/[.06]">
        @forelse($primaryResume?->aiAnalyses()->latest()->limit(8)->get() ?? [] as $analysis)
          <div class="py-3 text-xs">
            <div class="flex justify-between gap-3"><span class="font-medium text-zinc-200">{{ str_replace('_', ' ', $analysis->analysis_type) }}</span><span class="{{ $analysis->status === 'completed' ? 'text-emerald-300' : ($analysis->status === 'failed' ? 'text-rose-300' : 'text-amber-300') }}">{{ $analysis->status }}</span></div>
            <p class="mt-1 text-zinc-500">{{ $analysis->created_at->diffForHumans() }} · Score {{ $analysis->score ?? '--' }}</p>
          </div>
        @empty
          <p class="text-sm text-zinc-500">No AI history yet.</p>
        @endforelse
      </div>
    </div>
  </aside>
</section>
