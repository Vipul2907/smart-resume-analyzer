@php
  $resumes = $resumes ?? auth()->user()->resumes()->withCount('versions')->latest()->get();
  $selectedResume = $selectedResume ?? null;
  $activeResume = $selectedResume;
  $activeResume = $activeResume ?: $resumes->firstWhere('is_primary', true);
  $activeResume = $activeResume ?: $resumes->first();
  $currentVersion = $activeResume?->currentVersion();
  $content = $currentVersion?->content ?: [];
@endphp

<section class="grid gap-4 xl:grid-cols-[1fr_.48fr]">
  <div class="space-y-4">
    @forelse($resumes as $resume)
      <article class="card p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            <div class="grid h-11 w-10 place-items-center rounded border border-cyan-300/20 bg-cyan-400/10 text-xs font-bold text-cyan-200">{{ $resume->extensionLabel() }}</div>
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <h2 class="font-semibold">{{ $resume->name }}</h2>
                @if($resume->is_primary)<span class="rounded-full bg-emerald-400/10 px-2 py-1 text-[10px] font-bold text-emerald-300">PRIMARY</span>@endif
                <span class="rounded-full bg-white/[.06] px-2 py-1 text-[10px] text-zinc-400">{{ str_replace('_', ' ', strtoupper($resume->parse_status)) }}</span>
              </div>
              <p class="mt-1 text-xs text-zinc-500">{{ $resume->original_filename }} · {{ number_format($resume->file_size / 1024, 1) }} KB · Updated {{ $resume->updated_at->diffForHumans() }}</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <a href="{{ route('resumes.show', $resume) }}" class="btn btn-secondary">Details</a>
            <a href="{{ route('resumes.download', $resume) }}" class="btn btn-secondary">Download</a>
            <a href="/analyze" class="btn btn-primary">Analyze</a>
          </div>
        </div>
        <div class="mt-5 grid gap-2 border-t border-white/[.07] pt-4 text-center sm:grid-cols-3">
          <div><p class="text-lg font-semibold">{{ $resume->versions_count }}</p><p class="text-[10px] text-zinc-500">Saved versions</p></div>
          <div><p class="text-lg font-semibold text-cyan-300">{{ strlen((string) $resume->extracted_text) }}</p><p class="text-[10px] text-zinc-500">Text characters</p></div>
          <div><p class="text-lg font-semibold">{{ optional($resume->last_analyzed_at)->diffForHumans() ?? 'Not yet' }}</p><p class="text-[10px] text-zinc-500">Last AI analysis</p></div>
        </div>
      </article>
    @empty
      <article class="card border-dashed p-8 text-center">
        <p class="eyebrow">No resumes yet</p>
        <h2 class="mt-3 text-xl font-semibold">Upload your first resume.</h2>
        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500">SmartCV keeps files private, extracts readable text, and creates a structured version you can correct.</p>
        <a href="#upload-resume" class="btn btn-primary mt-5">Upload resume</a>
      </article>
    @endforelse

    @if($activeResume)
      <article class="card p-5 sm:p-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
          <div>
            <p class="eyebrow">Resume details</p>
            <h2 class="mt-2 text-xl font-semibold">{{ $activeResume->name }}</h2>
            <p class="mt-1 text-sm text-zinc-500">{{ $activeResume->parse_status === 'image_only' ? 'Image-only PDF detected. OCR can be added later.' : 'Extracted text and structured data are saved privately.' }}</p>
          </div>
          <div class="flex flex-wrap gap-2">
            <form method="POST" action="{{ route('resumes.primary', $activeResume) }}">@csrf<button class="btn btn-secondary" @disabled($activeResume->is_primary)>Set primary</button></form>
            <form method="POST" action="{{ route('resumes.parse', $activeResume) }}">@csrf<button class="btn btn-secondary">Re-parse</button></form>
          </div>
        </div>

        <div class="mt-6 grid gap-4 xl:grid-cols-[.42fr_.58fr]">
          <div class="space-y-4">
            <form method="POST" action="{{ route('resumes.update', $activeResume) }}" class="rounded-xl border border-white/[.08] p-4">
              @csrf @method('PATCH')
              <label class="block text-xs text-zinc-500">Resume name<input name="name" class="input mt-2" value="{{ old('name', $activeResume->name) }}" required></label>
              <button class="btn btn-secondary mt-3 w-full">Rename resume</button>
            </form>

            <form method="POST" action="{{ route('resumes.destroy', $activeResume) }}" class="rounded-xl border border-rose-400/20 bg-rose-400/5 p-4" onsubmit="return confirm('Delete this resume and its private file?')">
              @csrf @method('DELETE')
              <p class="text-sm font-medium text-rose-200">Delete safely</p>
              <p class="mt-1 text-xs leading-5 text-rose-100/70">This removes the private file and archived database record for your account.</p>
              <button class="btn mt-3 w-full border border-rose-400/30 bg-rose-400/10 text-rose-100">Delete resume</button>
            </form>

            <div class="rounded-xl border border-white/[.08] p-4">
              <p class="text-sm font-medium">Parsed sections</p>
              <div class="mt-3 flex flex-wrap gap-2 text-[10px] text-zinc-300">
                @foreach(['contact', 'summary', 'work_experience', 'education', 'skills', 'projects', 'certificates'] as $section)
                  <span class="rounded-full bg-white/[.06] px-2 py-1">{{ str_replace('_', ' ', $section) }}</span>
                @endforeach
              </div>
            </div>
          </div>

          <div class="space-y-4">
            @if($currentVersion)
              <form method="POST" action="{{ route('resume-versions.update', $currentVersion) }}" class="rounded-xl border border-white/[.08] p-4">
                @csrf @method('PATCH')
                <label class="block text-xs text-zinc-500">Version label<input name="label" class="input mt-2" value="{{ old('label', $currentVersion->label) }}" required></label>
                <label class="mt-3 block text-xs text-zinc-500">Summary<textarea name="summary" class="input mt-2 h-28 resize-y">{{ old('summary', $content['summary'] ?? '') }}</textarea></label>
                <label class="mt-3 block text-xs text-zinc-500">Skills, separated by commas<input name="skills" class="input mt-2" value="{{ old('skills', implode(', ', $content['skills'] ?? [])) }}"></label>
                <label class="mt-3 block text-xs text-zinc-500">Extracted text<textarea name="raw_text" class="input mt-2 h-56 resize-y">{{ old('raw_text', $content['raw_text'] ?? $activeResume->extracted_text) }}</textarea></label>
                <button class="btn btn-primary mt-4">Save corrections</button>
              </form>
            @else
              <div class="rounded-xl border border-amber-400/20 bg-amber-400/10 p-4 text-sm text-amber-100">No parsed version exists yet. Try re-parsing this resume.</div>
            @endif

            <div class="rounded-xl border border-white/[.08] p-4">
              <h3 class="text-sm font-semibold">Version history</h3>
              <div class="mt-3 divide-y divide-white/[.06]">
                @foreach($activeResume->versions()->latest()->get() as $version)
                  <div class="py-3 text-xs">
                    <div class="flex justify-between gap-3"><span class="font-medium text-zinc-200">v{{ $version->version_number }} · {{ $version->label }}</span><span class="text-zinc-500">{{ $version->created_at->diffForHumans() }}</span></div>
                    <p class="mt-1 text-zinc-500">{{ $version->change_summary }}</p>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      </article>
    @endif
  </div>

  <aside id="upload-resume" class="card h-max border-dashed p-5">
    <p class="eyebrow">Private upload</p>
    <h2 class="mt-2 font-semibold">Add a resume.</h2>
    <p class="mt-2 text-sm leading-6 text-zinc-500">PDF, DOCX, and TXT files up to 10 MB are stored outside the public web root.</p>
    <form method="POST" action="{{ route('resumes.store') }}" enctype="multipart/form-data" class="mt-5 space-y-4" data-upload-form>
      @csrf
      <label class="block text-xs text-zinc-500">Display name<input name="name" class="input mt-2" placeholder="Software Engineer Resume"></label>
      <label class="block text-xs text-zinc-500">Resume file<input name="resume_file" type="file" accept=".pdf,.docx,.txt" class="input mt-2" required data-upload-input></label>
      <div class="hidden rounded-xl border border-cyan-400/20 bg-cyan-400/10 p-3" data-upload-progress>
        <div class="flex justify-between text-xs text-cyan-100"><span data-upload-label>Preparing upload</span><span data-upload-percent>0%</span></div>
        <div class="mt-2 h-1.5 rounded-full bg-white/10"><div class="h-full w-0 rounded-full bg-cyan-300 transition-all" data-upload-bar></div></div>
      </div>
      <button class="btn btn-primary w-full">Upload and parse</button>
    </form>
    <p class="mt-3 text-center text-[10px] text-zinc-600">Only your account can access uploaded files.</p>
  </aside>
</section>
