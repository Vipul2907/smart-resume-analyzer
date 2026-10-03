<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Basic terms for using SmartCV’s career workspace.">
    <title>Terms · SmartCV</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen text-slate-900">
    <header class="border-b border-slate-200 bg-white/90">
        <nav class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5" aria-label="Main navigation"><a href="{{ route('home') }}" class="text-lg font-bold">SMART<span class="text-violet-600">CV</span></a>
            <div class="flex items-center gap-4 text-sm"><a href="{{ route('privacy') }}">Privacy</a>@auth<a class="btn btn-primary" href="{{ route('dashboard') }}">My workspace</a>@else<a href="{{ route('login') }}">Sign in</a>@endauth</div>
        </nav>
    </header>
    <main class="mx-auto max-w-3xl px-5 py-12 sm:py-16">
        <p class="eyebrow">SmartCV policies</p>
        <h1 class="mt-3 text-4xl font-semibold">Terms of use</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600">These plain-language terms describe responsible use of SmartCV. The service owner should review them for the laws and operating details that apply to the deployed service before launch.</p>
        <div class="card mt-8 space-y-8 p-6 sm:p-9">
            <section>
                <h2 class="text-lg font-semibold">Use your account responsibly</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Provide accurate account information, protect your sign-in details, and use the service lawfully. Do not try to access another person’s account, files, or private workspace data.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Your content</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">You remain responsible for the resumes, documents, and other material you upload or create. Upload only material you have the right to use. You control whether selected portfolio content and a resume are published in your public portfolio.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">AI and job information</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">AI guidance can be incomplete or incorrect. It is not an official applicant-tracking-system score, hiring decision, legal advice, or promise of employment. Check suggestions before using them. Job listings come from an outside public source and may change or become unavailable.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Availability and account controls</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">The service owner may need to maintain, update, or temporarily pause SmartCV. You can manage or delete your account through Settings, subject to the service’s technical and legal obligations.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Updates</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">The service owner should update these terms when the service or its operating rules change. Continued use after an update is subject to the terms displayed on the deployed service.</p>
            </section>
        </div>
        <p class="mt-6 text-xs leading-5 text-slate-500">Last reviewed: October 3, 2026. These starter terms are not a substitute for a legal review of the deployed service.</p>
    </main>
</body>

</html>