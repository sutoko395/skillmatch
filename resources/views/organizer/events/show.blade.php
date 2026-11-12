@extends('organizer.layouts.sidebar')

@section('title', $event->title)

@section('content')
    <nav class="mb-5 flex flex-wrap gap-4" aria-label="Rekrutmen dan pelaksanaan">
        <a class="font-semibold text-indigo-600" href="{{ route('organizer.applications.index', $event) }}">Pelamar</a>
        <a class="font-semibold text-indigo-600" href="{{ route('organizer.execution.show', $event) }}">Pelaksanaan dan kehadiran</a>
    </nav>
    <x-page-header
        :title="$event->title"
        description="Persetujuan admin, pembayaran dan publikasi merupakan tahap yang berbeda."
    />

    @if($event->cover_image_path)<x-event-cover :event="$event" class="mb-6" />@endif
    @php
        $editable =
            !$event->published_at &&
            in_array($event->status, ['draft', 'rejected']) &&
            $event->lifecycle_status === 'upcoming' &&
            $event->orders->isEmpty() &&
            !$event->entitlement;
    @endphp

    <ol
        aria-label="Tahap konfigurasi event"
        class="mb-6 flex flex-wrap gap-3 text-sm text-slate-600"
    >
        <li>1. Pilih paket</li>
        <li>2. Informasi event</li>
        <li>3. Posisi dan jadwal</li>
        <li>4. Assessment</li>
        <li>5. Tinjau dan ajukan</li>
    </ol>

    <div class="space-y-6">
        @include('organizer.events.coordination')

        @if(!$event->assessment_deadline && !$event->decision_deadline && in_array($event->status, ['pending', 'approved']) && $event->lifecycle_status === 'upcoming')
            <x-section-card>
                <h2 class="text-lg font-bold">Lengkapi tenggat event lama</h2>
                <p class="mt-2 text-sm text-slate-600">Isi sekali untuk membuka alur assessment. Jadwal kegiatan, paket, snapshot dan hasil lamaran tetap dipertahankan. Tenggat yang sudah diisi tidak dapat diubah lagi.</p>
                <form method="POST" action="{{ route('organizer.events.recruitment-repair', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <x-form-field name="assessment_deadline" label="Tenggat assessment (WIB)" type="datetime-local" :value="old('assessment_deadline')" required />
                    <x-form-field name="decision_deadline" label="Batas keputusan (WIB)" type="datetime-local" :value="old('decision_deadline')" required />
                    <x-form-field name="reason" label="Alasan pelengkapan" :value="old('reason')" required />
                    <label class="flex gap-2 text-sm"><input type="checkbox" name="confirmed" value="1" required> Saya mengonfirmasi tenggat ini untuk event dan lamaran lama yang belum mencatat tenggat.</label>
                    <x-button type="submit">Simpan tenggat event lama</x-button>
                </form>
            </x-section-card>
        @endif

        <x-section-card class="organizer-event-info">
            <h2 class="text-lg font-semibold">Informasi kegiatan</h2>
            <dl class="organizer-event-statuses">
                <div><dt>Moderasi</dt><dd><span>{{ $event->status }}</span></dd></div>
                <div><dt>Publikasi</dt><dd><span class="organizer-publication-status">{{ $event->publication_status }}</span></dd></div>
                <div><dt>Pelaksanaan</dt><dd><span>{{ $event->lifecycle_status }}</span></dd></div>
            </dl>

            @if ($event->verification_note)
                <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    Catatan admin: {{ $event->verification_note }}
                </p>
            @endif

            <dl class="organizer-event-meta">
                <div class="md:col-span-2">
                    <dt>Deskripsi kegiatan</dt>
                    <dd class="whitespace-pre-line">{{ $event->description }}</dd>
                </div>
                <div>
                    <dt>Lokasi</dt>
                    <dd>{{ $event->location }} &middot; {{ $event->city }}</dd>
                </div>
                <div>
                    <dt>Pelaksanaan (WIB)</dt>
                    <dd>{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                        - {{ $event->ends_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                </div>
            </dl>

            <div class="organizer-recruitment">
                <h3 class="mb-4 text-sm font-semibold">Jadwal rekrutmen</h3>
                <dl>
                    <div>
                        <dt>Deadline pendaftaran (WIB)</dt>
                        <dd>{{ $event->registration_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</dd>
                    </div>
                    <div>
                        <dt>Tenggat assessment (WIB)</dt>
                        <dd>{{ $event->assessment_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum diisi' }}</dd>
                    </div>
                    <div>
                        <dt>Batas akhir keputusan EO (WIB)</dt>
                        <dd>{{ $event->decision_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Belum diisi' }}</dd>
                    </div>
                </dl>

                @if ($event->published_at && !in_array($event->lifecycle_status, ['completed', 'cancelled']))
                    @if (!$event->assessment_deadline && !$event->decision_deadline)
                        <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Tenggat rekrutmen belum tersedia untuk event ini.</p>
                    @endif
                @endif
            </div>

            @if ($event->published_at && !in_array($event->lifecycle_status, ['completed', 'cancelled']))
                <a
                    href="{{ route('organizer.events.text', $event) }}"
                    class="organizer-secondary-button justify-self-start"
                >
                    Koreksi judul/deskripsi
                </a>
            @endif

            @if ($editable)
                <a
                    href="{{ route('organizer.events.edit', $event) }}"
                    class="organizer-secondary-button justify-self-start"
                >
                    Edit konfigurasi event
                </a>
            @endif
        </x-section-card>

        <x-section-card>
            <div class="organizer-section-heading">
                <h2 class="text-lg font-semibold">
                    Posisi dan jadwal
                </h2>

                @if ($editable)
                    <a
                        href="{{ route('organizer.events.positions.create', $event) }}"
                        class="organizer-secondary-button"
                    >
                        Tambah posisi
                    </a>
                @endif
            </div>

            @forelse ($event->positions as $position)
                <article
                    id="position-{{ $position->id }}"
                    class="organizer-position-card"
                >
                    <h3 class="font-semibold">
                        {{ $position->name }}
                    </h3>

                    <span class="mt-2 inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Kuota {{ $position->quota }}</span>

                    <p>
                        {{ $position->description }}
                    </p>

                    <div class="organizer-position-grid">
                        <div>
                            <h4 class="mb-2 text-xs font-medium text-slate-500">Skill dan level minimum</h4>
                            <p class="text-sm">
                                {{ $position->positionSkills
                                    ->map(fn ($s) => $s->skill->name . ' (' . $s->minimum_level . ')')
                                    ->join(', ') }}
                            </p>
                        </div>
                        <div>
                            <h4 class="mb-2 text-xs font-medium text-slate-500">Jadwal posisi (WIB)</h4>
                            <p class="text-sm font-semibold">{{ $position->follows_event_schedule ? 'Jadwal mengikuti event' : 'Jadwal tugas khusus' }}</p>
                            @foreach ($position->schedules as $slot)
                                <p class="text-sm">
                                    {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                    -
                                    {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                    WIB
                                </p>
                            @endforeach
                        </div>
                    </div>

                    @if($position->requirements->isNotEmpty())
                        <h4 class="mb-2 mt-5 text-xs font-medium text-slate-500">Persyaratan tambahan</h4>
                    @endif

                    @foreach ($position->requirements as $requirement)
                        <p class="mt-2 text-sm">
                            {{ $requirement->name }}:
                            {{ $requirement->description }}
                            ({{ $requirement->is_required ? 'wajib' : 'preferensi' }})

                            @if ($requirement->kind === 'document')
                                /
                                {{ $requirement->document_type }}
                            @endif
                        </p>
                    @endforeach

                    <div class="mt-3 flex flex-wrap gap-4">
                        @if ($editable)
                            <a
                                href="{{ route('organizer.events.positions.edit', [$event, $position]) }}"
                                class="organizer-secondary-button"
                            >
                                Edit posisi
                            </a>

                            <form
                                method="POST"
                                action="{{ route('organizer.events.positions.destroy', [$event, $position]) }}"
                                x-data
                                @submit="if (!confirm('Hapus posisi draft ini?')) $event.preventDefault()"
                            >
                                @csrf
                                @method('DELETE')

                                <button class="text-rose-700 underline">
                                    Hapus posisi
                                </button>
                            </form>
                        @endif

                    </div>
                </article>
            @empty
                <p class="mt-4 text-slate-600">
                    Belum ada posisi.
                </p>
            @endforelse
        </x-section-card>

        <x-section-card>
            <h2 id="assessment" class="organizer-section-heading text-lg font-semibold">Assessment</h2>
            @forelse($event->positions as $position)
                @php($assessment = $position->assessments->first())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div>
                        <p class="font-semibold">{{ $position->name }}</p>
                        <p class="text-sm text-slate-600">{{ $assessment?->is_published ? 'Dipublikasikan · Terkunci' : ($assessment ? 'Draft assessment' : 'Belum dibuat') }}</p>
                        @if($assessment)
                            <p class="mt-1 text-sm text-slate-700">{{ $assessment->questions_count }} soal · {{ $assessment->duration_minutes !== null ? $assessment->duration_minutes . ' menit' : 'Durasi belum diatur' }}</p>
                        @endif
                    </div>
                    <a href="{{ route('organizer.assessments.edit', $position) }}" class="organizer-secondary-button">{{ $assessment?->is_published || !$editable ? 'Lihat Assessment' : 'Kelola Assessment' }}</a>
                </div>
            @empty
                <p class="mt-4 text-sm text-slate-600">Tambahkan posisi di bagian Posisi dan jadwal terlebih dahulu untuk membuat assessment.</p>
            @endforelse
        </x-section-card>

        <x-section-card>
            <h2 class="organizer-section-heading text-lg font-semibold">
                Paket dan pembayaran
            </h2>

            @if ($event->package_snapshot)
                <p class="font-semibold">{{ $event->package_snapshot['name'] }}</p>
                <p class="mt-1 text-xl font-semibold">Rp {{ number_format($event->package_snapshot['price'], 0, ',', '.') }}</p>
                <dl class="organizer-package-summary mt-5">
                    <div><dt>Posisi</dt><dd class="text-sm font-semibold">{{ $event->package_snapshot['max_positions'] }}</dd></div>
                    <div><dt>Lamaran</dt><dd class="text-sm font-semibold">{{ $event->package_snapshot['max_applications'] }}</dd></div>
                    <div><dt>Maksimal hari pendaftaran</dt><dd class="text-sm font-semibold">{{ $event->package_snapshot['max_registration_days'] }}</dd></div>
                </dl>
            @else
                <p class="mt-3">
                    Paket belum dipilih.
                </p>
            @endif

            @if ($editable)
                <a
                    href="{{ route('organizer.packages.select', $event) }}"
                    class="organizer-secondary-button mt-4"
                >
                    Ubah paket
                </a>
            @endif

            @if ($event->entitlement)
                <p class="mt-4 rounded-xl bg-emerald-50 p-3 text-sm text-emerald-700">
                    Hak paket aktif sejak
                    {{ $event->entitlement->activated_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                    WIB.
                </p>
            @endif

            @if (
                $event->status === 'approved' &&
                ($event->package_snapshot['price'] ?? 0) > 0 &&
                !$event->entitlement &&
                $event->lifecycle_status === 'upcoming'
            )
                <form
                    class="mt-4"
                    method="POST"
                    action="{{ route('organizer.orders.store', $event) }}"
                >
                    @csrf

                    <x-button>
                        Buat/buka order Sandbox
                    </x-button>
                </form>
            @endif

            @foreach ($event->orders as $order)
                <p class="mt-3">
                    <a
                        href="{{ route('organizer.orders.show', $order) }}"
                        class="text-indigo-700 underline"
                    >
                        {{ $order->order_ref }}
                    </a>

                    &middot;

                    {{ $order->status }}
                </p>
            @endforeach
        </x-section-card>

        <x-section-card>
            <h2 class="text-lg font-semibold">
                Tindakan
            </h2>

            <div class="mt-4 flex flex-wrap gap-4">

                @if ($editable)
                    <form
                        method="POST"
                        action="{{ route('organizer.events.submit', $event) }}"
                    >
                        @csrf

                        <x-button>
                            Ajukan moderasi
                        </x-button>
                    </form>
                @endif

                @if (
                    $event->status === 'approved' &&
                    $event->lifecycle_status === 'upcoming' &&
                    $event->publication_status !== 'published'
                )
                    <form
                        method="POST"
                        action="{{ route('organizer.events.publish', $event) }}"
                    >
                        @csrf

                        <x-button>
                            Periksa syarat dan publikasikan
                        </x-button>
                    </form>
                @endif

                @if ($event->publication_status === 'published')
                    <a
                        href="{{ route('events.show', $event) }}"
                        class="organizer-secondary-button"
                    >
                        Lihat halaman publik
                    </a>
                @endif

                @if ($event->publication_status === 'published' || $event->entitlement)
                    <a
                        href="{{ route('organizer.applications.index', $event) }}"
                        class="organizer-secondary-button"
                    >
                        Kelola Pelamar Event &rarr;
                    </a>
                @endif
            </div>

            @if (in_array($event->lifecycle_status, ['upcoming', 'ongoing']))
                <form
                    class="organizer-event-cancel mt-6 space-y-3"
                    method="POST"
                    action="{{ route('organizer.events.cancel', $event) }}"
                    x-data
                    @submit="if (!confirm('Batalkan event beserta proses lamaran terkait?')) $event.preventDefault()"
                >
                    @csrf

                    <h3 class="text-sm font-semibold text-rose-700">Pembatalan event</h3>
                    <x-form-field
                        name="reason"
                        label="Alasan pembatalan"
                        required
                        minlength="5"
                        maxlength="1000"
                    />

                    <x-button variant="danger">
                        Batalkan event
                    </x-button>
                </form>

                <p class="mt-2 text-sm text-slate-600">
                    Pembatalan hanya berhasil setelah layanan lamaran dan assessment tersedia.
                </p>
            @endif

            @if (
                $editable &&
                !$event->submitted_at &&
                $event->positions->isEmpty() &&
                $event->status === 'draft'
            )
                <form
                    class="mt-6"
                    method="POST"
                    action="{{ route('organizer.events.destroy', $event) }}"
                    x-data
                    @submit="if (!confirm('Hapus draft awal yang belum memiliki posisi ini?')) $event.preventDefault()"
                >
                    @csrf
                    @method('DELETE')

                    <x-button variant="danger">
                        Hapus draft awal
                    </x-button>
                </form>
            @endif
        </x-section-card>

    </div>
@endsection
