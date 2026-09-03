<!doctype html>
<html lang="en" class="dark">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Portfolio Studio · SmartCV</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="min-h-screen bg-[#070b18] text-zinc-100">
<div class="min-h-screen"><x-workspace-sidebar active-screen="portfolio" />
<div class="min-h-screen lg:pl-64">
  <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9"><a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a><span class="hidden text-sm text-zinc-500 sm:block">Your private portfolio studio</span><a href="{{ route('profile') }}" class="text-sm font-medium">{{ auth()->user()->name }}</a></header>
  <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
    <p class="eyebrow">Career workspace</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Portfolio and public profile</h1><p class="mt-2 max-w-3xl text-sm text-zinc-400">Create honest case studies, choose exactly what is public, and share a recruiter-ready portfolio link.</p>
    @if(session('status'))<div class="mt-6 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-100">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="mt-6 rounded-xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm text-rose-100"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="mt-7 grid gap-5 xl:grid-cols-[.72fr_1.28fr]">
      <aside class="space-y-5">
        <article class="card p-5"><p class="eyebrow">New case study</p><h2 class="mt-2 font-semibold">Add a project</h2>
          <form method="POST" action="{{ route('portfolio.store') }}" enctype="multipart/form-data" class="mt-5 space-y-3">@csrf
            <label class="block text-xs text-zinc-400">Project title<input class="input mt-1" name="title" required></label>
            <label class="block text-xs text-zinc-400">Short description<input class="input mt-1" name="tagline" placeholder="What you built or improved"></label>
            <label class="block text-xs text-zinc-400">Your role<input class="input mt-1" name="role" placeholder="Founder, designer, developer"></label>
            <label class="block text-xs text-zinc-400">Project description<textarea class="input mt-1 h-24" name="description"></textarea></label>
            <label class="block text-xs text-zinc-400">Outcome or result<textarea class="input mt-1 h-20" name="outcome"></textarea></label>
            <label class="block text-xs text-zinc-400">Case study<textarea class="input mt-1 h-28" name="case_study" placeholder="Challenge, process, decisions, and what you learned."></textarea></label>
            <label class="block text-xs text-zinc-400">Technologies or skills <span class="text-zinc-600">(comma separated)</span><input class="input mt-1" name="skills"></label>
            <label class="block text-xs text-zinc-400">Live project link<input class="input mt-1" type="url" name="project_url" placeholder="https://"></label>
            <label class="block text-xs text-zinc-400">Repository link<input class="input mt-1" type="url" name="repository_url" placeholder="https://"></label>
            <label class="block text-xs text-zinc-400">Project image <span class="text-zinc-600">(max 5 MB)</span><input class="input mt-1" type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
            <label class="block text-xs text-zinc-400">Visibility<select class="input mt-1" name="visibility"><option value="private">Private — only me</option><option value="public">Public — show on my portfolio</option></select></label>
            <label class="flex gap-2 text-xs text-zinc-300"><input type="checkbox" name="is_featured" value="1"> Feature this project first</label><button class="btn btn-primary w-full">Save project</button>
          </form>
        </article>
        <article class="card p-5"><p class="eyebrow">Public profile settings</p><h2 class="mt-2 font-semibold">Share only what you choose</h2>
          <form method="POST" action="{{ route('portfolio.settings.update') }}" class="mt-5 space-y-3">@csrf @method('PATCH')
            <label class="block text-xs text-zinc-400">Public link name<input class="input mt-1" name="public_slug" value="{{ old('public_slug', $profile?->public_slug ?: \Illuminate\Support\Str::slug(auth()->user()->name)) }}" required><span class="mt-1 block text-zinc-600">Your link: {{ url('/p/') }}/your-link-name</span></label>
            <label class="block text-xs text-zinc-400">Recruiter contact email<input class="input mt-1" type="email" name="contact_email" value="{{ old('contact_email', $profile?->contact_email) }}"></label>
            <label class="flex gap-2 text-xs text-zinc-300"><input type="checkbox" name="portfolio_is_public" value="1" @checked(old('portfolio_is_public', $profile?->portfolio_is_public))> Publish my portfolio page</label>
            <label class="flex gap-2 text-xs text-zinc-300"><input type="checkbox" name="show_contact_email" value="1" @checked(old('show_contact_email', $profile?->show_contact_email))> Show recruiter contact button</label>
            <label class="flex gap-2 text-xs text-zinc-300"><input type="checkbox" name="show_resume" value="1" @checked(old('show_resume', $profile?->show_resume)) @disabled(! $primaryResume)> Let visitors download my primary resume</label><button class="btn btn-secondary w-full">Save sharing settings</button>
          </form>
          @if($profile?->portfolio_is_public && $profile->public_slug)<a class="btn btn-primary mt-3 w-full" href="{{ route('portfolio.public', $profile->public_slug) }}" target="_blank" rel="noreferrer">Open public portfolio</a>@endif
        </article>
      </aside>
      <div class="space-y-4">
        @forelse($projects as $project)
          <article class="card overflow-hidden"><div class="grid md:grid-cols-[180px_1fr]">
            @if($project->image_path)<img class="h-48 w-full object-cover md:h-full" src="{{ route('portfolio.image', $project) }}" alt="{{ $project->title }} project image">@else<div class="grid min-h-40 place-items-center bg-gradient-to-br from-violet-500/20 to-cyan-400/10 text-sm text-zinc-500">No image added</div>@endif
            <div class="p-5"><div class="flex justify-between gap-3"><div><h2 class="font-semibold">{{ $project->title }}</h2><p class="mt-1 text-xs text-cyan-200">{{ $project->role ?: 'Project' }} · {{ ucfirst($project->visibility ?? 'private') }}</p></div>@if($project->is_featured)<span class="rounded-full bg-violet-400/15 px-2 py-1 text-[10px] text-violet-200">FEATURED</span>@endif</div>
              <p class="mt-4 text-sm leading-6 text-zinc-400">{{ $project->description ?: $project->tagline ?: 'No description added yet.' }}</p>
              @if($project->outcome)<p class="mt-3 rounded-lg border border-emerald-400/15 bg-emerald-400/[.05] p-3 text-xs text-zinc-300"><strong class="text-emerald-200">Outcome:</strong> {{ $project->outcome }}</p>@endif
              @if($project->skills)<div class="mt-3 flex flex-wrap gap-2">@foreach($project->skills as $skill)<span class="rounded-full bg-white/[.06] px-2 py-1 text-xs text-zinc-300">{{ $skill }}</span>@endforeach</div>@endif
              <div class="mt-4 flex gap-3 text-xs">@if($project->project_url)<a class="text-cyan-200" href="{{ $project->project_url }}" target="_blank" rel="noreferrer">Open project</a>@endif @if($project->repository_url)<a class="text-cyan-200" href="{{ $project->repository_url }}" target="_blank" rel="noreferrer">Open repository</a>@endif</div>
              <details class="mt-4"><summary class="cursor-pointer text-xs text-violet-300">Edit project</summary><form method="POST" action="{{ route('portfolio.update', $project) }}" enctype="multipart/form-data" class="mt-4 grid gap-3 sm:grid-cols-2">@csrf @method('PATCH')
                <label class="text-xs text-zinc-400">Title<input class="input mt-1" name="title" value="{{ $project->title }}" required></label><label class="text-xs text-zinc-400">Role<input class="input mt-1" name="role" value="{{ $project->role }}"></label><label class="text-xs text-zinc-400 sm:col-span-2">Description<textarea class="input mt-1 h-20" name="description">{{ $project->description }}</textarea></label><label class="text-xs text-zinc-400">Outcome<textarea class="input mt-1 h-20" name="outcome">{{ $project->outcome }}</textarea></label><label class="text-xs text-zinc-400">Skills<input class="input mt-1" name="skills" value="{{ implode(', ', $project->skills ?? []) }}"></label><label class="text-xs text-zinc-400">Live link<input class="input mt-1" type="url" name="project_url" value="{{ $project->project_url }}"></label><label class="text-xs text-zinc-400">Repository link<input class="input mt-1" type="url" name="repository_url" value="{{ $project->repository_url }}"></label><label class="text-xs text-zinc-400 sm:col-span-2">Case study<textarea class="input mt-1 h-24" name="case_study">{{ $project->case_study }}</textarea></label><label class="text-xs text-zinc-400">Replace image<input class="input mt-1" type="file" name="image" accept="image/jpeg,image/png,image/webp"></label><label class="text-xs text-zinc-400">Visibility<select class="input mt-1" name="visibility"><option value="private" @selected($project->visibility === 'private')>Private</option><option value="public" @selected($project->visibility === 'public')>Public</option></select></label><label class="flex items-center gap-2 text-xs text-zinc-300"><input type="checkbox" name="is_featured" value="1" @checked($project->is_featured)> Feature project</label><button class="btn btn-secondary sm:col-span-2">Save project changes</button>
              </form></details><form method="POST" action="{{ route('portfolio.destroy', $project) }}" class="mt-4" onsubmit="return confirm('Remove this project and its private image?')">@csrf @method('DELETE')<button class="text-xs text-rose-200">Remove project</button></form>
            </div>
          </div></article>
        @empty
          <div class="card border-dashed p-10 text-center text-sm text-zinc-400">No portfolio projects yet. Add an honest case study that you would be comfortable discussing with a recruiter.</div>
        @endforelse
      </div>
    </section>
  </main>
</div></div></body></html>
