@extends('organizer.layouts.sidebar')

@section('title', $event->title)

@section('content')
    <x-page-header
        :title="$event->title"
        description="Persetujuan admin, pembayaran dan publikasi merupakan tahap yang berbeda."
    />

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

        <x-section-card>
            <p>
                Moderasi:
                <strong>{{ $event->status }}</strong>
                &middot;
                Publikasi:
                <strong>{{ $event->publication_status }}</strong>
                &middot;
                Pelaksanaan:
                <strong>{{ $event->lifecycle_status }}</strong>
            </p>

            @if ($event->verification_note)
                <p class="mt-3">
                    Catatan admin: {{ $event->verification_note }}
                </p>
            @endif

            <p class="mt-3 whitespace-pre-line">
                {{ $event->description }}
            </p>

            <p class="mt-3">
                {{ $event->location }}
                &middot;
                {{ $event->city }}
            </p>

            <p>
                {{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                -
                {{ $event->ends_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                WIB
            </p>

            <p>
                Deadline:
                {{ $event->registration_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                WIB
            </p>

            @if ($event->published_at && !in_array($event->lifecycle_status, ['completed', 'cancelled']))
                <a
                    href="{{ route('organizer.events.text', $event) }}"
                    class="mt-4 inline-block text-indigo-700 underline"
                >
                    Koreksi judul/deskripsi
                </a>
            @endif

            @if ($editable)
                <a
                    href="{{ route('organizer.events.edit', $event) }}"
                    class="mt-4 inline-block text-indigo-700 underline"
                >
                    Edit konfigurasi event
                </a>
            @endif
        </x-section-card>

        <x-section-card>
            <div class="flex flex-wrap justify-between gap-3">
                <h2 class="text-xl font-semibold">
                    Posisi dan jadwal
                </h2>

                @if ($editable)
                    <a
                        href="{{ route('organizer.events.positions.create', $event) }}"
                        class="text-indigo-700 underline"
                    >
                        Tambah posisi
                    </a>
                @endif
            </div>

            @forelse ($event->positions as $position)
                <article
                    id="position-{{ $position->id }}"
                    class="mt-4 border-t border-slate-200 pt-4"
                >
                    <h3 class="font-semibold">
                        {{ $position->name }}
                        &middot;
                        Kuota {{ $position->quota }}
                    </h3>

                    <p>
                        {{ $position->description }}
                    </p>

                    <p class="text-sm">
                        {{ $position->positionSkills
                            ->map(fn ($s) => $s->skill->name . ' (' . $s->minimum_level . ')')
                            ->join(', ') }}
                    </p>

                    <p class="mt-2 text-sm text-slate-600">{{ $position->follows_event_schedule ? 'Jadwal mengikuti event' : 'Jadwal tugas khusus' }}</p>
                    @foreach ($position->schedules as $slot)
                        <p class="text-sm">
                            {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            -
                            {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                            WIB
                        </p>
                    @endforeach

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
                                class="text-indigo-700 underline"
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
            <h2 id="assessment" class="text-xl font-semibold">Assessment</h2>
            @forelse($event->positions as $position)
                @php($assessment = $position->assessments->first())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                    <div>
                        <p class="font-semibold">{{ $position->name }}</p>
                        <p class="text-sm text-slate-600">{{ $assessment?->is_published ? 'Dipublikasikan · Terkunci' : ($assessment ? 'Draft assessment' : 'Belum dibuat') }}</p>
                        @if($assessment)
                            <p class="mt-1 text-sm text-slate-700">{{ $assessment->questions_count }} soal · {{ $assessment->duration_minutes !== null ? $assessment->duration_minutes . ' menit' : 'Durasi belum diatur' }}</p>
                        @endif
                    </div>
                    <a href="{{ route('organizer.assessments.edit', $position) }}" class="text-sm font-semibold text-indigo-700 underline">{{ $assessment?->is_published || !$editable ? 'Lihat Assessment' : 'Kelola Assessment' }}</a>
                </div>
            @empty
                <p class="mt-4 text-sm text-slate-600">Tambahkan posisi di bagian Posisi dan jadwal terlebih dahulu untuk membuat assessment.</p>
            @endforelse
        </x-section-card>

        <x-section-card>
            <h2 class="text-xl font-semibold">
                Paket dan pembayaran
            </h2>

            @if ($event->package_snapshot)
                <p class="mt-3">
                    {{ $event->package_snapshot['name'] }}
                    &middot;
                    Rp {{ number_format($event->package_snapshot['price'], 0, ',', '.') }}
                </p>

                <p>
                    {{ $event->package_snapshot['max_positions'] }} posisi;
                    {{ $event->package_snapshot['max_applications'] }} lamaran;
                    maksimal {{ $event->package_snapshot['max_registration_days'] }} hari pendaftaran.
                </p>
            @else
                <p class="mt-3">
                    Paket belum dipilih.
                </p>
            @endif

            @if ($editable)
                <a
                    href="{{ route('organizer.packages.select', $event) }}"
                    class="mt-3 inline-block text-indigo-700 underline"
                >
                    Ubah paket
                </a>
            @endif

            @if ($event->entitlement)
                <p class="mt-3 text-emerald-700">
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
            <h2 class="text-xl font-semibold">
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
                        class="text-indigo-700 underline"
                    >
                        Lihat halaman publik
                    </a>
                @endif

                @if ($event->publication_status === 'published' || $event->entitlement)
                    <a
                        href="{{ route('organizer.applications.index', $event) }}"
                        class="inline-flex items-center rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
                    >
                        Kelola Pelamar Event &rarr;
                    </a>
                @endif
            </div>

            @if (in_array($event->lifecycle_status, ['upcoming', 'ongoing']))
                <form
                    class="mt-6 space-y-3"
                    method="POST"
                    action="{{ route('organizer.events.cancel', $event) }}"
                    x-data
                    @submit="if (!confirm('Batalkan event beserta proses lamaran terkait?')) $event.preventDefault()"
                >
                    @csrf

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
