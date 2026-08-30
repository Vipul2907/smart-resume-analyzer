<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Interview Lab · SmartCV</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
<div class="min-h-screen">
  <x-workspace-sidebar active-screen="interviews" />
  <div class="min-h-screen lg:pl-64">
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
      <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a>
      <p class="hidden text-sm text-zinc-500 sm:block">Private interview practice</p>
      <a href="{{ route('profile') }}" class="text-sm font-medium">{{ auth()->user()->name }}</a>
    </header>
    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
      <p class="eyebrow">Career workspace</p>
      <h1 class="mt-2 text-3xl font-semibold">Advanced interview lab</h1>
      <p class="mt-2 text-sm text-zinc-400">Practice realistic questions, save answers, replay private recordings, and receive readiness coaching.</p>

      @if(session('status'))
        <div class="mt-6 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-100">{{ session('status') }}</div>
      @endif
      @if($errors->any())
        <div class="mt-6 rounded-xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm text-rose-100">
          @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
      @endif

      <section class="mt-7 grid gap-5 xl:grid-cols-[.7fr_1.3fr]">
        <aside class="card h-max p-5">
          <p class="eyebrow">Practice setup</p><h2 class="mt-2 font-semibold">Start a focused session</h2>
          <form method="POST" action="{{ route('interviews.store') }}" class="mt-5 space-y-3">
            @csrf
            <label class="block text-xs text-zinc-400">Session title<input class="input mt-1" name="title" required placeholder="Backend Engineer mock interview"></label>
            <label class="block text-xs text-zinc-400">Target role<input class="input mt-1" name="target_role" value="{{ auth()->user()->target_role }}"></label>
            <label class="block text-xs text-zinc-400">Company <span class="text-zinc-600">(optional)</span><input class="input mt-1" name="company_name"></label>
            <label class="block text-xs text-zinc-400">Interview type<select class="input mt-1" name="session_type"><option value="general">General</option><option value="technical">Technical</option><option value="behavioral">Behavioural</option><option value="hr">HR</option><option value="leadership">Leadership</option><option value="case_study">Case study</option></select></label>
            <label class="block text-xs text-zinc-400">Minutes<input class="input mt-1" type="number" min="5" max="180" name="duration_minutes" value="30"></label>
            <label class="block text-xs text-zinc-400">Reminder <span class="text-zinc-600">(optional)</span><input class="input mt-1" type="datetime-local" name="reminder_at"></label>
            <label class="block text-xs text-zinc-400">Related application<select class="input mt-1" name="job_application_id"><option value="">None</option>@foreach($jobs as $job)<option value="{{ $job->id }}">{{ $job->company }} - {{ $job->role }}</option>@endforeach</select></label>
            <button class="btn btn-primary w-full">Start practice</button>
          </form>
        </aside>
        <div class="space-y-4">
          @forelse($interviews as $interview)
            @php($questions = $interview->questions ?? [])
            @php($hasAnswers = collect($interview->responses ?? [])->filter(fn ($answer) => trim((string) $answer) !== '')->isNotEmpty())
            <article class="card p-5">
              <div class="flex flex-wrap justify-between gap-4">
                <div>
                  <h2 class="font-semibold">{{ $interview->title }}</h2>
                  <p class="mt-1 text-sm text-zinc-400 capitalize">{{ $interview->session_type ?: $interview->type ?: 'general' }} · {{ $interview->duration_minutes ?? 0 }} minutes</p>
                  @if($interview->company_name)<p class="mt-1 text-xs text-zinc-500">{{ $interview->company_name }}</p>@endif
                  @if($interview->reminder_at)<p class="mt-1 text-xs text-cyan-200">Reminder: {{ $interview->reminder_at->format('M j, Y g:i A') }}</p>@endif
                </div>
                <span class="rounded-full bg-white/[.06] px-3 py-1 text-xs capitalize">{{ str_replace('_', ' ', $interview->status) }}</span>
              </div>

              @if(count($questions) && $interview->status !== 'completed')
                <form method="POST" action="{{ route('interviews.responses.update', $interview) }}" class="mt-5 space-y-3">
                  @csrf @method('PATCH')
                  @foreach($questions as $index => $question)
                    <label class="block rounded-xl border border-white/[.08] p-4 text-sm font-medium">{{ $index + 1 }}. {{ $question }}<textarea class="input mt-3 h-28 resize-y text-sm font-normal" name="answers[{{ $index }}]">{{ old('answers.'.$index, $interview->responses[$index] ?? '') }}</textarea></label>
                  @endforeach
                  <button class="btn btn-secondary">Save answers</button>
                </form>
              @endif

              <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-white/[.08] p-3">
                  <p class="text-xs text-zinc-400">Private audio/video recording</p>
                  @if($interview->recording_path)
                    @if(str_starts_with((string) $interview->recording_mime_type, 'audio/'))
                      <audio class="mt-3 w-full" controls src="{{ route('interviews.recordings.play', $interview) }}"></audio>
                    @else
                      <video class="mt-3 max-h-56 w-full rounded-lg" controls src="{{ route('interviews.recordings.play', $interview) }}"></video>
                    @endif
                    <a href="{{ route('interviews.recordings.download', $interview) }}" class="mt-3 inline-block text-xs text-violet-300">Download recording</a>
                  @else
                    <form method="POST" enctype="multipart/form-data" action="{{ route('interviews.recordings.store', $interview) }}" class="mt-3">
                      @csrf
                      <input class="input !py-2 text-xs" type="file" name="recording" accept="audio/*,video/mp4,video/webm,video/quicktime" required>
                      <button class="mt-3 text-xs text-cyan-200">Save recording to replay later</button>
                    </form>
                  @endif
                </div>
                <div class="rounded-xl border border-white/[.08] p-3">
                  @if($interview->status !== 'completed')
                    <p class="text-xs text-zinc-400">Readiness score</p>
                    @if($hasAnswers)
                      <form method="POST" action="{{ route('interviews.complete', $interview) }}" class="mt-3">@csrf @method('PATCH')<button class="btn btn-primary">Calculate readiness score</button></form>
                    @else
                      <p class="mt-3 text-sm leading-6 text-zinc-500">Save at least one written answer before calculating a score. The score is based on your saved answers, not a number you type.</p>
                    @endif
                  @elseif($hasAnswers)
                    <p class="text-sm text-emerald-200">Readiness score: {{ $interview->score ?? 0 }}/100</p>
                    @foreach(($interview->feedback['improvements'] ?? []) as $note)<p class="mt-2 text-xs leading-5 text-zinc-400">{{ $note }}</p>@endforeach
                  @else
                    <p class="text-sm text-zinc-500">This older session was completed without saved answers, so no readiness score is available.</p>
                  @endif
                </div>
              </div>
              <form method="POST" action="{{ route('interviews.destroy', $interview) }}" class="mt-4" onsubmit="return confirm('Remove this saved practice session?')">@csrf @method('DELETE')<button class="text-xs text-rose-200">Remove session</button></form>
            </article>
          @empty
            <div class="card border-dashed p-10 text-center text-sm text-zinc-400">No sessions yet. Start a practice round to receive five focused questions.</div>
          @endforelse
        </div>
      </section>
    </main>
  </div>
</div>
</body>
</html>
