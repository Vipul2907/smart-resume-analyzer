<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cover letters · SmartCV</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
  <header class="border-b border-white/[.08] bg-[#090e20]/95 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-8">
      <a href="{{ route('resumes') }}" class="text-sm font-semibold text-zinc-300">← My resumes</a>
      <a href="{{ route('cover-letters.create') }}" class="btn btn-primary">Create cover letter</a>
    </div>
  </header>
  <main class="mx-auto max-w-7xl px-5 py-9 sm:px-8">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
      <div><p class="eyebrow">Step 3 · Cover letter builder</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Letters tailored to real opportunities.</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-400">Create a focused letter for each role, connect the resume you used, and export a clean document when it is ready.</p></div>
      <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to workspace</a>
    </div>
    @if(session('status'))<div class="mt-6 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-100">{{ session('status') }}</div>@endif
    <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      @forelse($letters as $letter)
        <article class="card flex min-h-64 flex-col p-5">
          <div class="flex items-start justify-between gap-4"><div><p class="text-[10px] font-bold uppercase tracking-[.16em] text-violet-300">{{ ucfirst($letter->template) }} template</p><h2 class="mt-2 text-lg font-semibold">{{ $letter->title }}</h2><p class="mt-1 text-sm text-zinc-500">{{ $letter->company_name ?: 'Company not added' }}{{ $letter->job_title ? ' · '.$letter->job_title : '' }}</p></div><span class="rounded-full bg-white/[.06] px-3 py-1 text-xs capitalize text-zinc-300">{{ $letter->status }}</span></div>
          <p class="mt-5 line-clamp-3 text-sm leading-6 text-zinc-400">{{ $letter->body }}</p>
          <div class="mt-auto flex flex-wrap gap-2 border-t border-white/[.07] pt-5"><a href="{{ route('cover-letters.edit', $letter) }}" class="btn btn-primary">Edit letter</a><a href="{{ route('cover-letters.preview', $letter) }}" target="_blank" class="btn btn-secondary">Preview</a><form method="POST" action="{{ route('cover-letters.destroy', $letter) }}" onsubmit="return confirm('Remove this cover letter?')">@csrf @method('DELETE')<button class="btn border border-rose-400/25 text-rose-200">Remove</button></form></div>
        </article>
      @empty
        <article class="card border-dashed p-10 text-center md:col-span-2 xl:col-span-3"><p class="eyebrow">Your first letter</p><h2 class="mt-3 text-xl font-semibold">Write a letter that explains your fit.</h2><p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-zinc-500">Start from a thoughtful template, then make it personal with your own achievements and motivation.</p><a href="{{ route('cover-letters.create') }}" class="btn btn-primary mt-5">Create your first cover letter</a></article>
      @endforelse
    </section>
  </main>
</body>
</html>
