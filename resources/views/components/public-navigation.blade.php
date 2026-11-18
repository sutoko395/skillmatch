<header class="eb-header eb-header-dark" x-data="{ menuOpen: false }">
    <nav class="eb-container eb-navigation" aria-label="Navigasi utama">
        <a href="{{ route('home') }}" aria-label="SkillMatch beranda"><x-public-brand /></a>
        <button type="button" class="eb-menu-toggle" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen" aria-controls="public-menu">Menu <span aria-hidden="true">☰</span></button>
        <div id="public-menu" class="eb-menu" :class="{ 'is-open': menuOpen, 'is-collapsed': !menuOpen }" @keydown.escape.window="menuOpen = false">
            <a href="{{ route('events.index') }}" @if(request()->routeIs('events.*')) aria-current="page" @endif>Jelajahi event</a>
            <a href="{{ route('home') }}#cara-kerja" @click="menuOpen = false">Cara kerja</a>
            <a href="{{ route('home') }}#untuk-organizer" @click="menuOpen = false">Untuk Organizer</a>
            @auth
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}">Panel Admin</a>
                @else
                    <a href="{{ route(auth()->user()->role.'.activity.index') }}">{{ auth()->user()->role === 'organizer' ? 'Panel Organizer' : 'Panel Volunteer' }}</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="eb-register-mobile">@csrf<button type="submit" class="eb-button eb-button-small eb-register-cta">Keluar</button></form>
            @else
                <a href="{{ route('login') }}">Masuk</a>
                <a href="{{ route('register') }}" class="eb-button eb-button-small eb-register-cta eb-register-mobile">Buat akun <x-arrow-icon direction="up-right" /></a>
            @endauth
        </div>
        @auth
            <form method="POST" action="{{ route('logout') }}" class="eb-register-desktop">@csrf<button type="submit" class="eb-button eb-button-small eb-register-cta">Keluar</button></form>
        @endauth
        @guest
            <a href="{{ route('register') }}" class="eb-button eb-button-small eb-register-cta eb-register-desktop">Buat akun <x-arrow-icon direction="up-right" /></a>
        @endguest
    </nav>
</header>
