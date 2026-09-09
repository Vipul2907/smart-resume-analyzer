@props(['activeScreen' => 'dashboard'])

@php
    $groups = [
        'Home' => [
            ['dashboard', 'Overview', '⌘'],
        ],
        'Create' => [
            ['resumes', 'My resumes', '▤'],
            ['cover-letters', 'Cover letters', '✉'],
            ['portfolio', 'Portfolio', '◇'],
        ],
        'Improve' => [
            ['ats', 'ATS optimizer', '◈'],
            ['match', 'Job match', '↔'],
            ['skills', 'Skill studio', '◇'],
            ['learning-paths', 'Learning paths', '◫'],
        ],
        'Apply' => [
            ['discover', 'Job discovery', '⌕'],
            ['jobs', 'Job tracker', '◎'],
            ['interviews', 'Interview lab', '◌'],
        ],
        'Review' => [
            ['insights', 'Career insights', '↗'],
            ['analytics', 'Analytics', '⌁'],
            ['documents', 'Document vault', '▣'],
            ['notifications', 'Reminders', '◉'],
        ],
    ];
@endphp

<aside class="workspace-sidebar fixed inset-y-0 z-40 hidden w-64 border-r p-4 lg:block">
    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-2 py-3 text-lg font-extrabold tracking-tight text-[#17223a]">
        <span class="grid h-10 w-10 place-items-center rounded-2xl bg-gradient-to-br from-violet-500 via-indigo-500 to-cyan-400 text-xl text-white shadow-lg shadow-violet-200">✦</span>
        SMART<span class="text-violet-500">CV</span>
    </a>

    <nav class="mt-6 space-y-5 pb-6" aria-label="Workspace navigation">
        @foreach($groups as $group => $items)
            <section>
                <p class="px-2 text-[10px] font-extrabold uppercase tracking-[.18em] text-zinc-500">{{ $group }}</p>
                <div class="mt-2 space-y-1">
                    @foreach($items as [$route, $label, $icon])
                        <a href="/{{ $route }}" class="nav-link {{ $activeScreen === $route ? 'active' : '' }}">
                            <span class="grid w-4 place-items-center text-sm" aria-hidden="true">{{ $icon }}</span>
                            <span>{{ $label }}</span>
                            @if($route === 'interviews')
                                <span class="ml-auto rounded-full bg-violet-100 px-1.5 py-0.5 text-[9px] font-extrabold text-violet-700">AI</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>

    <div class="mt-6 border-t border-slate-200 pt-5">
        <p class="px-2 text-[10px] font-extrabold uppercase tracking-[.18em] text-zinc-500">Account</p>
        <a href="{{ route('profile') }}" class="nav-link mt-2 {{ $activeScreen === 'profile' ? 'active' : '' }}"><span class="w-4 text-center">○</span>Profile</a>
        <a href="{{ route('settings') }}" class="nav-link {{ $activeScreen === 'settings' ? 'active' : '' }}"><span class="w-4 text-center">⚙</span>Settings</a>
        @if(auth()->user()?->is_admin)
            <a href="{{ route('admin.index') }}" class="nav-link {{ $activeScreen === 'admin' ? 'active' : '' }}"><span class="w-4 text-center">◆</span>Admin area</a>
        @endif
        <a href="{{ route('help') }}" class="nav-link"><span class="w-4 text-center">?</span>Help center</a>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button class="nav-link w-full text-left"><span class="w-4 text-center">↪</span>Sign out</button>
        </form>
    </div>
</aside>

<button type="button" data-workspace-drawer-toggle class="fixed left-4 top-4 z-50 inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700 shadow-lg shadow-slate-300/30 lg:hidden" aria-controls="workspace-drawer" aria-expanded="false">Menu</button>
<div id="workspace-drawer" data-workspace-drawer class="fixed inset-0 z-[60] hidden lg:hidden" aria-hidden="true">
    <button type="button" data-workspace-drawer-close class="absolute inset-0 h-full w-full bg-slate-950/20" aria-label="Close navigation"></button>
    <aside class="relative h-full w-[min(19rem,86vw)] overflow-y-auto border-r border-slate-200 bg-white p-5 shadow-2xl">
        <div class="flex items-center justify-between"><a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-extrabold text-slate-900"><span class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-cyan-400 text-white">✦</span>SMART<span class="text-violet-500">CV</span></a><button type="button" data-workspace-drawer-close class="rounded-lg p-2 text-slate-500" aria-label="Close navigation">Close</button></div>
        <nav class="mt-7 space-y-5" aria-label="Mobile workspace navigation">
            @foreach($groups as $group => $items)
                <section><p class="px-2 text-[10px] font-extrabold uppercase tracking-[.18em] text-zinc-500">{{ $group }}</p><div class="mt-2 space-y-1">@foreach($items as [$route, $label, $icon])<a href="/{{ $route }}" class="nav-link {{ $activeScreen === $route ? 'active' : '' }}"><span class="w-4 text-center">{{ $icon }}</span>{{ $label }}</a>@endforeach</div></section>
            @endforeach
            <section class="border-t border-slate-200 pt-5"><p class="px-2 text-[10px] font-extrabold uppercase tracking-[.18em] text-zinc-500">Account</p><a href="{{ route('profile') }}" class="nav-link mt-2">Profile</a><a href="{{ route('settings') }}" class="nav-link">Settings</a><a href="{{ route('help') }}" class="nav-link">Help center</a></section>
        </nav>
    </aside>
</div>
