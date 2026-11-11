@props(['event', 'detailRoute' => 'events.show'])
<article class="eb-event-card">
    <a class="eb-event-cover-link" href="{{ route($detailRoute, $event) }}" aria-label="Lihat kegiatan {{ $event->title }}"><x-event-cover :event="$event" /></a>
    <div class="eb-event-category"><span>{{ $event->category?->name ?? 'Kegiatan relawan' }}</span><span>{{ $event->positions_count }} posisi</span></div>
    <h3><a href="{{ route($detailRoute, $event) }}">{{ $event->title }}</a></h3>
    <p class="eb-event-organizer">{{ $event->organizer->organizerProfile?->organization_name ?? $event->organizer->name }} · {{ $event->cityRecord?->name ?? $event->city }}</p>
    <p class="eb-event-description">{{ Str::limit($event->description, 140) }}</p>
    <dl class="eb-event-meta"><div><dt>Pelaksanaan</dt><dd>{{ $event->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div><div><dt>Batas pendaftaran</dt><dd>{{ $event->registration_deadline->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd></div></dl>
    <a class="eb-event-action" href="{{ route($detailRoute, $event) }}">Lihat posisi dan persyaratan <span aria-hidden="true">↗︎</span></a>
</article>
