    <header class="border-b border-slate-200 bg-white">
        <nav aria-label="Navigasi utama" class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-700">SkillMatch</a>
            <div class="flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ route('events.index') }}">Jelajahi Event</a>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
                    @else
                        <a href="{{ route(auth()->user()->role.'.activity.index') }}">Aktivitas</a>
                        <a href="{{ route(auth()->user()->role.'.profile.edit') }}">Profil</a>
                    @endif
                    @if(auth()->user()->role === 'volunteer')<a href="{{ route('volunteer.applications.index') }}">Lamaran Saya</a>@endif
                    @if(auth()->user()->role === 'organizer' && auth()->user()->organizer_status === 'active')<a href="{{ route('organizer.events.index') }}">Event Saya</a>@endif
                    @unless(auth()->user()->hasVerifiedEmail())<a href="{{ route('verification.notice') }}">Verifikasi email</a>@endunless
                    <form method="POST" action="{{ route('logout') }}">@csrf <x-secondary-button type="submit">Keluar</x-secondary-button></form>
                @else
                    <a href="{{ route('login') }}">Masuk</a><a href="{{ route('register') }}">Daftar</a>
                @endauth
            </div>
        </nav>
    </header>
