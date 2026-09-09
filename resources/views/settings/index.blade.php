<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Settings · SmartCV</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-[#070b18] text-zinc-100">
  <div class="min-h-screen"><x-workspace-sidebar active-screen="settings" />
    <div class="min-h-screen lg:pl-64">
      <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9"><a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-300">CV</span></a>
        <p class="hidden text-sm text-zinc-500 sm:block">Account and privacy controls</p><a href="{{ route('profile') }}" class="flex items-center gap-2 text-sm font-medium"><span class="grid h-9 w-9 place-items-center rounded-full bg-gradient-to-br from-violet-400 to-cyan-300 text-slate-950">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>{{ auth()->user()->name }}</a>
      </header>
      <main class="mx-auto max-w-5xl px-5 py-8 lg:px-9">
        <p class="eyebrow">Account privacy and security</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight">Control your SmartCV account.</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">Your career data is private by default. Use these controls to manage reminders, AI consent, security, and your data.</p>
        @if(session('status'))<div class="mt-6 rounded-xl border border-emerald-400/25 bg-emerald-400/10 p-4 text-sm text-emerald-100">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="mt-6 rounded-xl border border-rose-400/25 bg-rose-400/10 p-4 text-sm text-rose-100">
          <p class="font-semibold">Please check the form.</p>
          <ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>@endif
        <section class="mt-7 grid gap-5 lg:grid-cols-2">
          <article class="card p-5 sm:p-6">
            <p class="eyebrow">Notifications</p>
            <h2 class="mt-2 text-lg font-semibold">Reminder preferences</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Choose how SmartCV should remind you about follow-ups, interviews, and goals.</p>
            <form method="POST" action="{{ route('settings.preferences.update') }}" class="mt-5 space-y-4">@csrf @method('PATCH')
              <label class="flex gap-3 rounded-xl border border-white/[.08] p-4 text-sm"><input type="checkbox" name="in_app_reminders" value="1" @checked($preferences->in_app_reminders)><span><strong class="block">In-app reminders</strong><span class="mt-1 block text-xs leading-5 text-zinc-500">Show private follow-up, interview, and goal reminders inside SmartCV.</span></span></label>
              <label class="flex gap-3 rounded-xl border border-white/[.08] p-4 text-sm"><input type="checkbox" name="email_reminders" value="1" @checked($preferences->email_reminders)><span><strong class="block">Email reminder preference</strong><span class="mt-1 block text-xs leading-5 text-zinc-500">Save your preference for career reminders. Security and verification emails remain enabled.</span></span></label>
              <label class="flex gap-3 rounded-xl border border-white/[.08] p-4 text-sm"><input type="checkbox" name="weekly_career_review" value="1" @checked($preferences->weekly_career_review)><span><strong class="block">Weekly career review</strong><span class="mt-1 block text-xs leading-5 text-zinc-500">Save a weekly review preference for your career progress.</span></span></label>
              <input type="hidden" name="ai_processing_enabled" value="{{ $preferences->ai_processing_enabled ? 1 : 0 }}"><input type="hidden" name="retain_ai_history" value="{{ $preferences->retain_ai_history ? 1 : 0 }}"><button class="btn btn-primary w-full">Save reminder preferences</button>
            </form>
          </article>
          <article class="card p-5 sm:p-6">
            <p class="eyebrow">AI privacy</p>
            <h2 class="mt-2 text-lg font-semibold">AI processing controls</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Groq receives resume text only when you actively request an analysis and accept its consent checkbox.</p>
            <form method="POST" action="{{ route('settings.preferences.update') }}" class="mt-5 space-y-4">@csrf @method('PATCH')
              <label class="flex gap-3 rounded-xl border border-white/[.08] p-4 text-sm"><input type="checkbox" name="ai_processing_enabled" value="1" @checked($preferences->ai_processing_enabled)><span><strong class="block">Allow AI analysis</strong><span class="mt-1 block text-xs leading-5 text-zinc-500">Turn this off to block all future resume analysis and job-match requests.</span></span></label>
              <label class="flex gap-3 rounded-xl border border-white/[.08] p-4 text-sm"><input type="checkbox" name="retain_ai_history" value="1" @checked($preferences->retain_ai_history)><span><strong class="block">Keep AI analysis history</strong><span class="mt-1 block text-xs leading-5 text-zinc-500">Turning this off immediately deletes your currently saved AI analysis history.</span></span></label>
              <input type="hidden" name="in_app_reminders" value="{{ $preferences->in_app_reminders ? 1 : 0 }}"><input type="hidden" name="email_reminders" value="{{ $preferences->email_reminders ? 1 : 0 }}"><input type="hidden" name="weekly_career_review" value="{{ $preferences->weekly_career_review ? 1 : 0 }}"><button class="btn btn-primary w-full">Save AI privacy controls</button>
            </form>
          </article>
          <article class="card p-5 sm:p-6">
            <p class="eyebrow">Security</p>
            <h2 class="mt-2 text-lg font-semibold">Change password</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Use at least eight characters. Changing it securely signs out other browsers using this account.</p>
            <form method="POST" action="{{ route('settings.password.update') }}" class="mt-5 space-y-3">@csrf @method('PATCH')<label class="block text-xs text-zinc-400">Current password<input class="input mt-1" type="password" name="current_password" autocomplete="current-password" required></label><label class="block text-xs text-zinc-400">New password<input class="input mt-1" type="password" name="password" autocomplete="new-password" required></label><label class="block text-xs text-zinc-400">Confirm new password<input class="input mt-1" type="password" name="password_confirmation" autocomplete="new-password" required></label><button class="btn btn-secondary w-full">Change password and sign out other devices</button></form>
          </article>
          <article class="card p-5 sm:p-6">
            <p class="eyebrow">Your data</p>
            <h2 class="mt-2 text-lg font-semibold">Download and manage files</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Download your structured workspace data, or manage uploaded resumes and private files before you decide to delete anything.</p>
            <div class="mt-5 grid gap-3"><a class="btn btn-secondary w-full" href="{{ route('analytics.data.export') }}">Download my personal data</a><a class="btn btn-secondary w-full" href="{{ route('documents.index') }}">Open document vault</a><a class="btn btn-secondary w-full" href="{{ route('resumes') }}">Manage resumes and files</a></div>
          </article>
        </section>
        <section class="mt-5 card border border-rose-400/25 p-5 sm:p-6">
          <p class="eyebrow text-rose-200">Danger zone</p>
          <h2 class="mt-2 text-lg font-semibold">Delete SmartCV account</h2>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-zinc-400">This permanently removes your account, saved workspace records, and private uploaded files. This cannot be undone. Download your personal data first if you want a copy.</p>
          <form method="POST" action="{{ route('settings.destroy') }}" class="mt-5 grid gap-3 sm:grid-cols-3">@csrf @method('DELETE')<label class="block text-xs text-zinc-400">Current password<input class="input mt-1" type="password" name="current_password" autocomplete="current-password" required></label><label class="block text-xs text-zinc-400">Type DELETE to confirm<input class="input mt-1" name="confirmation" autocomplete="off" required></label>
            <div class="flex items-end"><button class="btn w-full border border-rose-400/35 bg-rose-400/10 text-rose-100 hover:bg-rose-400/20" onclick="return confirm('Delete your account and all private SmartCV data permanently?')">Delete account</button></div>
          </form>
        </section>
        <section class="mt-5 grid gap-5 md:grid-cols-2">
          <article class="card p-5">
            <h2 class="font-semibold">Privacy promise</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Your profile, resumes, applications, interviews, skills, and files belong to you. They are private unless you explicitly publish selected portfolio content.</p><a href="{{ route('privacy') }}" class="mt-4 inline-block text-sm text-cyan-300 underline">Read privacy center</a>
          </article>
          <article class="card p-5">
            <h2 class="font-semibold">Account security</h2>
            <p class="mt-2 text-sm leading-6 text-zinc-500">Use a unique password, do not share verification links, and sign out after using a shared computer.</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">@csrf<button class="text-sm text-cyan-300 underline">Sign out of this browser</button></form>
          </article>
        </section>
      </main>
    </div>
  </div>
</body>

</html>
