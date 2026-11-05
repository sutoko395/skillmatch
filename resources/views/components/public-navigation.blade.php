<header class="eb-header {{ request()->routeIs('home') ? 'eb-header-dark' : '' }}" x-data="{ menuOpen: false }">
    <nav class="eb-container eb-navigation" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" aria-label="SkillMatch beranda"><x-public-brand /></a>
        <button type="button" class="eb-menu-toggle" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="public-menu">Menu <span aria-hidden="true">☰</span></button>
        <div id="public-menu" class="eb-menu" :class="{ 'is-open': menuOpen, 'is-collapsed': !menuOpen }" @keydown.escape.window="menuOpen = false">
            <a href="{{ route('events.index') }}" @if(request()->routeIs('events.*')) aria-current="page" @endif>Jelajahi event</a>
            <a href="{{ route('home') }}#cara-kerja">Cara kerja</a>
            <a href="{{ route('home') }}#untuk-organizer">Untuk Organizer</a>
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
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="eb-button eb-button-small">Keluar</button></form>
            @else
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}" class="eb-button eb-button-small">Buat akun <span aria-hidden="true">↗︎</span></a>
            @endauth
        </div>
    </nav>
</header>
