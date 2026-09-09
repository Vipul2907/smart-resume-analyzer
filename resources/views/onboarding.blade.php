<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up your workspace · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink text-zinc-100">
    <main class="mx-auto grid min-h-screen max-w-2xl place-items-center p-5">
        <section class="card glow w-full p-7 sm:p-10">
            <div class="flex items-center justify-between gap-4"><a href="{{ route('home') }}" class="text-sm font-extrabold">SMART<span class="text-violet-500">CV</span></a><span class="rounded-full bg-violet-50 px-3 py-1 text-[10px] font-extrabold tracking-wider text-violet-700">FREE FOREVER</span></div>
            <p class="eyebrow mt-10">Set up your workspace</p>
            <h1 class="mt-3 text-3xl font-semibold tracking-tight">Where do you want to go next, {{ auth()->user()->name }}?</h1>
            <p class="mt-3 max-w-xl text-sm leading-6 text-slate-600">This takes less than a minute. Your choices help SmartCV make the dashboard, learning plan, and recommendations relevant from day one.</p>
            <form method="POST" action="{{ route('onboarding.store') }}" class="mt-8 space-y-6">
                @csrf
                <label class="block text-sm font-semibold">Your target role<select name="target_role" class="input mt-2" required><option value="">Choose a role</option><option>Product Designer</option><option>Software Engineer</option><option>Data Analyst</option><option>Marketing Manager</option><option>Product Manager</option><option>Other</option></select></label>
                <fieldset><legend class="text-sm font-semibold">Your current career stage</legend><div class="mt-3 grid gap-2 sm:grid-cols-2">@foreach(['student' => 'Student or graduate','early' => 'Early career','mid' => 'Mid-level','senior' => 'Senior professional','leader' => 'Leader or manager'] as $value => $label)<label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3 text-sm text-slate-700 transition hover:border-violet-300 hover:bg-violet-50"><input type="radio" name="experience_level" value="{{ $value }}" required class="accent-violet-600"><span>{{ $label }}</span></label>@endforeach</div></fieldset>
                <button class="btn btn-primary w-full py-3">Build my free workspace</button>
            </form>
            @if($errors->any())<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>@endif
        </section>
    </main>
</body>
</html>
