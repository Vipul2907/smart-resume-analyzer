<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify your email · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink text-zinc-100">
    <main class="grid min-h-screen place-items-center p-5">
        <section class="card glow w-full max-w-md p-7 text-center sm:p-9">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-violet-100 to-cyan-100 text-xl text-violet-700">✉</div>
            <p class="eyebrow mt-7">One last step</p>
            <h1 class="mt-3 text-2xl font-semibold">Verify your email.</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">We sent a secure verification link to <span class="font-semibold text-slate-800">{{ auth()->user()->email }}</span>. Open it to activate your free workspace.</p>
            @if(session('status'))<p class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('status') }}</p>@endif
            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">@csrf<button class="btn btn-secondary w-full">Send a new verification link</button></form>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf<button class="text-xs font-semibold text-slate-500 hover:text-violet-700">Sign out</button></form>
        </section>
    </main>
</body>
</html>
