<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ucfirst($screen) }} · SmartCV</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $titles = [
        'resumes' => ['Your resumes', 'Keep every version polished, targeted, and ready to send.'],
        'analyze' => ['Resume intelligence', 'Review what is working, what is missing, and where to focus next.'],
        'ats' => ['ATS optimizer', 'Improve clarity, structure, and readiness before a recruiter sees your resume.'],
        'match' => ['Job match', 'Compare one selected resume with a real opportunity and improve it truthfully.'],
        'privacy' => ['Privacy center', 'Clear controls and a simple commitment to your data.'],
        'terms' => ['Terms of service', 'The agreements that keep SmartCV fair and reliable.'],
    ];
    [$title, $subtitle] = $titles[$screen] ?? ['SmartCV', 'Your private career workspace.'];
    $activeScreen = $screen === 'analyze' ? 'resumes' : $screen;
@endphp
<body class="min-h-screen bg-[#070b18] text-zinc-100">
<div class="min-h-screen">
    <x-workspace-sidebar :active-screen="$activeScreen" />
    <div class="min-h-screen lg:pl-64">
        <header class="sticky top-0 z-20 flex items-center justify-between border-b border-white/[.07] bg-[#090e20]/95 px-5 py-4 backdrop-blur lg:px-9">
            <a href="{{ route('dashboard') }}" class="font-bold lg:hidden">SMART<span class="text-violet-500">CV</span></a>
            <div class="hidden w-full max-w-sm sm:block"><label class="relative block"><span class="pointer-events-none absolute left-3 top-2.5 text-slate-400">⌕</span><input class="input py-2 pl-9 text-sm" placeholder="Search your workspace" aria-label="Search your workspace"></label></div>
            <a href="{{ route('profile') }}" class="text-sm font-semibold">{{ auth()->user()->name }}</a>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8 lg:px-9">
            <div class="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div><p class="eyebrow">{{ in_array($screen, ['privacy', 'terms'], true) ? 'SmartCV legal' : 'Career workspace' }}</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $subtitle }}</p></div>
                @if($screen === 'resumes')<a href="#upload-resume" class="btn btn-primary">Upload resume</a>@endif
            </div>

            @if(session('status'))<div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('status') }}</div>@endif
            @if(session('error'))<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">{{ session('error') }}</div>@endif
            @if($errors->any())<div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700"><ul class="list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            @if($screen === 'resumes')
                @include('resumes.index')
            @elseif(in_array($screen, ['analyze', 'ats'], true))
                @include('analyze.foundation')
            @elseif($screen === 'match')
                @include('analyze.match')
            @else
                <section class="mx-auto max-w-3xl"><article class="card p-6 sm:p-9"><p class="text-sm leading-7 text-slate-600">{{ $screen === 'privacy' ? 'SmartCV is built around a simple promise: your professional story belongs to you. We collect only the information needed to provide your private career workspace, protect it carefully, and give you meaningful control.' : 'These terms explain how SmartCV provides a reliable and respectful career workspace. By using the product, you agree to use it responsibly and to keep your account information accurate.' }}</p><div class="mt-8 space-y-7">@foreach($screen === 'privacy' ? [['What we collect','Profile information, documents you choose to upload, workspace activity, and limited technical data required to keep the service secure.'],['How we use it','To provide your workspace, maintain your saved work, improve reliability, and communicate important account information.'],['Your choices','You can update, download, or request deletion of personal data from Settings.']] : [['Using SmartCV','Use the platform for lawful professional purposes and keep your sign-in details private.'],['Your content','You retain ownership of documents and career content you add. SmartCV processes it only to provide the service.'],['Free access','SmartCV provides its core career tools free of charge.']] as [$heading, $body])<div><h2 class="text-base font-semibold text-slate-900">{{ $heading }}</h2><p class="mt-2 text-sm leading-6 text-slate-600">{{ $body }}</p></div>@endforeach</div></article></section>
            @endif
        </main>
    </div>
</div>
</body>
</html>
