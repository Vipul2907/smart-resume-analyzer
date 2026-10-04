<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="How SmartCV handles account data, private files, public portfolios, and optional AI processing.">
    <title>Privacy · SmartCV</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen text-slate-900">
    @include('components.skip-link')
    <header class="border-b border-slate-200 bg-white/90">
        <nav class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5" aria-label="Main navigation"><a
                href="{{ route('home') }}" class="text-lg font-bold">SMART<span class="text-violet-600">CV</span></a>
            <div class="flex items-center gap-4 text-sm"><a href="{{ route('terms') }}">Terms</a>@auth<a
                    class="btn btn-primary" href="{{ route('dashboard') }}">My workspace</a>@else<a
                    href="{{ route('login') }}">Sign in</a>@endauth
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-3xl px-5 py-12 sm:py-16" id="main-content" tabindex="-1">
        <p class="eyebrow">SmartCV policies</p>
        <h1 class="mt-3 text-4xl font-semibold">Privacy</h1>
        <p class="mt-4 text-sm leading-6 text-slate-600">This page describes how SmartCV handles information in its
            career workspace. The service owner should keep it aligned with the services enabled in the deployment.</p>
        <div class="card mt-8 space-y-8 p-6 sm:p-9">
            <section>
                <h2 class="text-lg font-semibold">Information you save</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">SmartCV stores account details such as your name and
                    email, career profile details, and the resumes, applications, notes, interview practice, skills,
                    goals, documents, and portfolio projects you choose to save. Uploaded files are private unless you
                    choose to publish portfolio content or share a resume through your public portfolio settings.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Optional AI tools</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">When you choose an AI feature and accept its notice,
                    SmartCV sends the selected resume text and the job or career information needed for that request to
                    the configured Groq service. When Keep AI analysis history is on, SmartCV saves the result in your
                    account. When it is off, the result is shown once in your current session and then discarded. Do not
                    submit information you do not want processed by that provider. You can also block future AI
                    processing in Settings.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Other services</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">The live job board requests public listing information
                    from Arbeitnow. Opening an employer listing takes you to that employer’s website. The deployed
                    service may also use hosting, email, database, file storage, and monitoring providers; the service
                    owner should identify those providers here when selected.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Public sharing</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">A portfolio is public only when you enable it. Only
                    projects marked public are displayed. A resume is included only when you separately enable resume
                    sharing. Anyone with the public portfolio link can view its published information.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Your controls</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">You can edit or remove saved items, change privacy
                    preferences, export personal data, and delete your account from Settings. Account deletion removes
                    account workspace records and associated private files managed by SmartCV.</p>
            </section>
            <section>
                <h2 class="text-lg font-semibold">Questions and updates</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">For account help, use the Help area after signing in.
                    The service owner should provide a working contact address and update this page when data handling
                    or connected providers change.</p>
            </section>
        </div>
        <p class="mt-6 text-xs leading-5 text-slate-500">Last reviewed: October 3, 2026. The service owner should review
            this page against the actual hosting setup before public launch.</p>
    </main>
</body>

</html>
