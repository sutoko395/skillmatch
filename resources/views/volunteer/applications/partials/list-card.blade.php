<article class="vl-card" aria-labelledby="application-title-{{ $application->id }}">
    <div class="vl-card-heading">
        <div class="min-w-0">
            <h2 id="application-title-{{ $application->id }}">{{ $application->event->title }}</h2>
            <p class="vl-position">Posisi: {{ $application->position->name }}</p>
        </div>
        <div class="vl-status"><x-application-status :application="$application" /></div>
    </div>
    <dl class="vl-location">
        <dt>Lokasi kegiatan</dt>
        <dd>{{ $application->event->location }} @if($application->event->cityRecord?->name) &middot; {{ $application->event->cityRecord->name }} @endif</dd>
    </dl>
    <div class="vl-card-footer">
        <dl class="vl-dates">
            <div>
                <dt>Dibuat</dt>
                <dd>{{ $application->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
            </div>
            @if($application->submitted_at)
                <div>
                    <dt>Dikirim</dt>
                    <dd>{{ $application->submitted_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                </div>
            @endif
        </dl>
        <a href="{{ route('volunteer.applications.show', $application) }}" class="vl-detail" aria-label="Lihat Detail: {{ $application->event->title }}">Lihat Detail</a>
    </div>
</article>
