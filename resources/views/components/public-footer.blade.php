<footer class="eb-footer">
    <div class="eb-container">
        <div class="eb-footer-row">
            <div class="eb-footer-identity">
                <a href="{{ route('home') }}" aria-label="SkillMatch beranda"><x-public-brand /></a>
                <p>Temukan ruang untuk keterampilan,<br>waktu, dan niat baik Anda.</p>
            </div>
            <nav class="eb-footer-links" aria-label="Navigasi footer">
                <div>
                    <h2>Jelajahi SkillMatch</h2>
                    <ul>
                        <li><a href="{{ route('events.index') }}">Katalog event</a></li>
                        <li><a href="{{ route('home') }}#cara-kerja">Cara kerja</a></li>
                        <li><a href="{{ route('home') }}#untuk-organizer">Untuk Organizer</a></li>
                    </ul>
                </div>
                <div>
                    <h2>Akun</h2>
                    <ul>
                        @guest
                            <li><a href="{{ route('login') }}">Masuk</a></li>
                            <li><a href="{{ route('register') }}">Buat akun</a></li>
                        @else
                            <li><a href="{{ route('dashboard') }}">Akun saya</a></li>
                            <li><a href="{{ route('notifications.index') }}">Notifikasi</a></li>
                        @endguest
                    </ul>
                </div>
            </nav>
        </div>
        <div class="eb-footer-bottom">
            <p class="eb-footer-note">&copy; {{ date('Y') }} SkillMatch Volunteer</p>
        </div>
    </div>
</footer>
