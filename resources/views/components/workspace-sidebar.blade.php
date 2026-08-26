@props(['activeScreen' => 'dashboard'])

@php
    $navigation = [
        ['dashboard', 'Overview', '⌘'],
        ['resumes', 'My resumes', '▤'],
        ['match', 'Job match', '↔'],
        ['cover-letters', 'Cover letters', '✉'],
        ['jobs', 'Job tracker', '◎'],
        ['interviews', 'Interview lab', '◌'],
        ['skills', 'Skill studio', '◇'],
        ['insights', 'Career insights', '↗'],
        ['portfolio', 'Portfolio', '◇'],
        ['analytics', 'Analytics', '⌁'],
    ];
@endphp

<aside class="workspace-sidebar fixed inset-y-0 z-40 hidden w-64 border-r border-white/[.07] bg-[#090e20] p-4 lg:block">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 py-3 text-lg font-bold tracking-tight text-white">
        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 via-violet-500 to-cyan-400 text-xl text-white shadow-lg shadow-violet-500/20">✦</span>
        SMART<span class="text-violet-300">CV</span>
    </a>

    <p class="mt-7 px-2 text-[10px] font-bold uppercase tracking-[.18em] text-zinc-600">Workspace</p>
    <nav class="mt-4 space-y-1">
        @foreach($navigation as [$route, $label, $icon])
            <a href="/{{ $route }}" class="nav-link flex items-center gap-3 {{ $activeScreen === $route ? 'active' : '' }}">
                <span class="grid w-4 place-items-center text-sm text-zinc-400" aria-hidden="true">{{ $icon }}</span>
                <span>{{ $label }}</span>
                @if($route === 'interviews')
                    <span class="ml-auto rounded bg-violet-500/20 px-1.5 py-0.5 text-[9px] font-bold text-violet-200">NEW</span>
                @endif
            </a>
        @endforeach
    </nav>

    <div class="mt-6 border-t border-white/[.07] pt-5">
        <p class="px-2 text-[10px] font-bold uppercase tracking-[.18em] text-zinc-600">Account</p>
        <a href="{{ route('profile') }}" class="nav-link mt-3 flex items-center gap-3 {{ $activeScreen === 'profile' ? 'active' : '' }}"><span class="w-4 text-center text-zinc-400">○</span>Profile</a>
        <a href="{{ route('settings') }}" class="nav-link flex items-center gap-3 {{ $activeScreen === 'settings' ? 'active' : '' }}"><span class="w-4 text-center text-zinc-400">⚙</span>Settings</a>
        <a href="{{ route('help') }}" class="nav-link flex items-center gap-3"><span class="w-4 text-center text-zinc-400">?</span>Help center</a>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="nav-link flex w-full items-center gap-3 text-left"><span class="w-4 text-center text-zinc-400">↪</span>Sign out</button>
        </form>
    </div>
</aside>
