@extends('organizer.layouts.sidebar')

@section('title', $event->title)
@section('topbar-title', 'Kegiatan Saya')

@section('content')
    <div class="organizer-event-detail">
    <a href="{{ route('organizer.events.index') }}" class="organizer-action-button organizer-detail-back mb-4">Kembali</a>
    <x-page-header
        :title="$event->title"
    />

    @php
        $editable =
            !$event->published_at &&
            in_array($event->status, ['draft', 'rejected']) &&
            $event->lifecycle_status === 'upcoming' &&
            $event->orders->isEmpty() &&
            !$event->entitlement;
    @endphp

    @if($editable)
        <form method="POST" enctype="multipart/form-data" action="{{ route('organizer.events.cover.update', $event) }}"
            class="organizer-cover-editor mb-6"
            x-data="eventCoverUpload(@js($event->cover_image_path ? route('events.cover', $event) : null))"
            @submit="saving = true">
            @csrf
            @method('PATCH')
            <img x-show="preview" :src="preview || undefined" @if($event->cover_image_path) src="{{ route('events.cover', $event) }}" @endif
                alt="Foto sampul {{ $event->title }}" class="organizer-detail-cover organizer-cover-preview" @if(!$event->cover_image_path) x-cloak @endif>
            <div class="organizer-cover-controls">
                <input type="file" name="cover_image" x-ref="file" class="sr-only" tabindex="-1" accept="image/jpeg,image/png,image/webp"
                    aria-label="Pilih gambar kegiatan" aria-describedby="detail-cover-help detail-cover-error" @change="choose($event)">
                <div class="organizer-cover-picker">
                <button type="button" class="organizer-secondary-button organizer-info-edit" @click="$refs.file.click()" :disabled="saving">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15l-1 6 5-2" /></svg>
                    <span>{{ $event->cover_image_path ? 'Ubah gambar' : 'Tambah gambar' }}</span>
                </button>
                    <p id="detail-cover-help" class="mt-2 text-xs leading-relaxed text-slate-500">JPG, PNG, atau WebP, maksimal 5 MB.<br>Disarankan 1600 &times; 900 px (16:9).</p>
                </div>
                <div x-show="objectUrl" x-cloak class="organizer-cover-confirm">
                    <button type="submit" class="organizer-action-button" :disabled="saving"><span x-text="saving ? 'Menyimpan...' : 'Simpan'">Simpan</span></button>
                    <button type="button" class="organizer-secondary-button organizer-info-edit" @click="cancel()" :disabled="saving">Batal</button>
                </div>
            </div>
            <p id="detail-cover-error" class="mt-2 text-xs text-red-600" role="alert"><span x-text="error"></span>@error('cover_image') {{ $message }} @enderror</p>
            @error('event')<p class="mt-2 text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
            <noscript><a href="{{ route('organizer.events.edit', $event) }}#cover_image">Ubah gambar melalui form edit</a></noscript>
        </form>
    @elseif($event->cover_image_path)
        <x-event-cover :event="$event" class="organizer-detail-cover mb-6" />
    @endif

    <nav
        class="organizer-section-nav"
        aria-label="Bagian detail kegiatan"
        x-data="{
            active: 'ringkasan',
            holdUntil: 0,
            selectSection(section) {
                this.active = section;
                this.holdUntil = Date.now() + 500;
            },
            updateActive() {
                if (window.scrollY > 0 && Date.now() < this.holdUntil) return;
                const sections = [...this.$el.querySelectorAll('a[href]')]
                    .map(link => document.getElementById(link.hash.slice(1)))
                    .filter(Boolean);
                const boundary = this.$el.getBoundingClientRect().bottom + 24;
                const passed = sections.filter(section => section.getBoundingClientRect().top <= boundary);
                const atBottom = window.scrollY > 0 && window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;
                this.active = (atBottom ? sections.at(-1) : passed.at(-1))?.id ?? 'ringkasan';
            }
        }"
        x-init="$nextTick(() => updateActive())"
        @scroll.window.throttle.100ms="updateActive()"
        @resize.window.debounce.100ms="updateActive()"
        @hashchange.window="if ([...$el.querySelectorAll('a')].some(link => link.hash === window.location.hash)) selectSection(window.location.hash.slice(1))"
    >
        @foreach(['ringkasan' => 'Ringkasan', 'posisi' => 'Posisi & jadwal', 'assessment' => 'Asesmen', 'koordinasi-peserta' => 'Koordinasi peserta', 'paket' => 'Paket & pembayaran', 'tindakan' => 'Tindakan'] as $section => $label)
            <a href="#{{ $section }}" @click="selectSection('{{ $section }}')" :aria-current="active === '{{ $section }}' ? 'location' : null">{{ $label }}</a>
        @endforeach
    </nav>
    <div class="space-y-6">

        @if(!$event->assessment_deadline && !$event->decision_deadline && in_array($event->status, ['pending', 'approved']) && $event->lifecycle_status === 'upcoming')
            <x-section-card>
                <h2 class="text-lg font-bold">Lengkapi tenggat kegiatan lama</h2>
                <p class="mt-2 text-sm text-slate-600">Isi sekali untuk membuka alur asesmen. Jadwal kegiatan, paket, snapshot dan hasil lamaran tetap dipertahankan. Tenggat yang sudah diisi tidak dapat diubah lagi.</p>
                <form method="POST" action="{{ route('organizer.events.recruitment-repair', $event) }}" class="mt-4 space-y-4">
                    @csrf
                    <x-form-field name="assessment_deadline" label="Tenggat asesmen (WIB)" type="datetime-local" :value="old('assessment_deadline')" required />
                    <x-form-field name="decision_deadline" label="Batas keputusan (WIB)" type="datetime-local" :value="old('decision_deadline')" required />
                    <x-form-field name="reason" label="Alasan pelengkapan" :value="old('reason')" required />
                    <label class="flex gap-2 text-sm"><input type="checkbox" name="confirmed" value="1" required> Saya mengonfirmasi tenggat ini untuk kegiatan dan lamaran lama yang belum mencatat tenggat.</label>
                    <x-button type="submit">Simpan tenggat kegiatan lama</x-button>
                </form>
            </x-section-card>
        @endif

        <x-section-card id="ringkasan" class="organizer-event-info">
            <h2 class="text-lg font-semibold">Informasi kegiatan</h2>

            @if ($event->verification_note)
                <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900">
                    Catatan admin: {{ $event->verification_note }}
                </p>
            @endif

            <dl class="organizer-event-meta organizer-info-subcard">
                <div class="organizer-info-description md:col-span-2">
                    <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 0v6h6M8 13h8m-8 4h6" /></svg><span>Deskripsi kegiatan</span></dt>
                    <dd class="whitespace-pre-line">{{ $event->description }}</dd>
                </div>
                <div>
                    <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" /><circle cx="12" cy="10" r="2.5" /></svg><span>Lokasi</span></dt>
                    <dd>{{ $event->location }} &middot; {{ $event->city }}</dd>
                </div>
                <div>
                    <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg><span>Pelaksanaan (WIB)</span></dt>
                    <dd class="organizer-info-range">
                        <span>{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum diisi' }}<span class="organizer-info-time">{{ $event->starts_at?->setTimezone('Asia/Jakarta')->format('H:i') }}{{ $event->starts_at ? ' WIB' : '' }}</span></span>
                        <span class="organizer-info-range-separator">sampai</span>
                        <span>{{ $event->ends_at?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum diisi' }}<span class="organizer-info-time">{{ $event->ends_at?->setTimezone('Asia/Jakarta')->format('H:i') }}{{ $event->ends_at ? ' WIB' : '' }}</span></span>
                    </dd>
                </div>
            </dl>

            <div class="organizer-recruitment organizer-info-subcard">
                <h3 class="mb-4 text-sm font-semibold">Jadwal rekrutmen</h3>
                <dl>
                    <div>
                        <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3v4m8-4v4M4 10h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" /></svg><span>Tenggat pendaftaran (WIB)</span></dt>
                        <dd>
                            <span>{{ $event->registration_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum diisi' }}</span>
                            @if($event->registration_deadline)<span class="organizer-info-time">{{ $event->registration_deadline->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>@endif
                        </dd>
                    </div>
                    <div>
                        <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3M9 3h6v4H9V3Zm-1 9h8m-8 4h5" /></svg><span>Tenggat asesmen (WIB)</span></dt>
                        <dd>
                            <span>{{ $event->assessment_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum diisi' }}</span>
                            @if($event->assessment_deadline)<span class="organizer-info-time">{{ $event->assessment_deadline->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>@endif
                        </dd>
                    </div>
                    <div>
                        <dt><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6" /></svg><span>Batas keputusan seleksi (WIB)</span></dt>
                        <dd>
                            <span>{{ $event->decision_deadline?->setTimezone('Asia/Jakarta')->format('d/m/Y') ?? 'Belum diisi' }}</span>
                            @if($event->decision_deadline)<span class="organizer-info-time">{{ $event->decision_deadline->setTimezone('Asia/Jakarta')->format('H:i') }} WIB</span>@endif
                        </dd>
                    </div>
                </dl>

                @if ($event->published_at && !in_array($event->lifecycle_status, ['completed', 'cancelled']))
                    @if (!$event->assessment_deadline && !$event->decision_deadline)
                        <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-800">Tenggat rekrutmen belum tersedia untuk kegiatan ini.</p>
                    @endif
                @endif
            </div>

            @if ($event->published_at && !in_array($event->lifecycle_status, ['completed', 'cancelled']))
                <a
                    href="{{ route('organizer.events.text', $event) }}"
                    class="organizer-secondary-button organizer-info-edit justify-self-start"
                    aria-label="Edit judul dan deskripsi kegiatan"
                    title="Edit judul dan deskripsi"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15l-1 6 5-2" /></svg>
                    <span>Edit</span>
                </a>
            @endif

            @if ($editable)
                <a
                    href="{{ route('organizer.events.edit', $event) }}"
                    class="organizer-secondary-button organizer-info-edit justify-self-start"
                    aria-label="Edit informasi kegiatan"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15l-1 6 5-2" /></svg>
                    <span>Edit</span>
                </a>
            @endif
        </x-section-card>

        <x-section-card id="posisi">
            <div class="organizer-section-heading">
                <h2 class="text-lg font-semibold">
                    Posisi dan jadwal
                </h2>

                @if ($editable)
                    <a
                        href="{{ route('organizer.events.positions.create', $event) }}"
                        class="organizer-secondary-button organizer-info-edit"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                        <span>Tambah posisi</span>
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

                    <div class="organizer-position-grid">
                        <div class="organizer-position-subcard">
                            <h4 class="organizer-position-subtitle">Deskripsi &amp; persyaratan</h4>
                            @if($position->description)
                                <p class="organizer-position-description whitespace-pre-line">{{ $position->description }}</p>
                            @endif
                            <div class="mt-4">
                                <h5 class="mb-2 text-xs font-medium text-slate-500">Skill dan level minimum</h5>
                                <p class="text-sm">
                                    {{ $position->positionSkills
                                        ->map(fn ($s) => $s->skill->name . ' (' . $s->minimum_level . ')')
                                        ->join(', ') }}
                                </p>
                            </div>
                            @if($position->requirements->isNotEmpty())
                                <h5 class="mb-2 mt-5 text-xs font-medium text-slate-500">Persyaratan tambahan</h5>
                            @endif
                            @foreach ($position->requirements as $requirement)
                                <p class="mt-2 text-sm">
                                    {{ $requirement->name }}:
                                    {{ $requirement->description }}
                                    ({{ $requirement->is_required ? 'wajib' : 'preferensi' }})
                                    @if ($requirement->kind === 'document')
                                        / {{ $requirement->document_type }}
                                    @endif
                                </p>
                            @endforeach
                        </div>
                        <div class="organizer-position-subcard">
                            <h4 class="organizer-position-subtitle">Jadwal posisi (WIB)</h4>
                            <p class="mb-3 mt-3 text-xs font-medium text-slate-500">{{ $position->follows_event_schedule ? 'Jadwal mengikuti kegiatan' : 'Jadwal tugas khusus' }}</p>
                            @foreach ($position->schedules as $slot)
                                <p class="mt-2 text-sm">
                                    {{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                    -
                                    {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}
                                    WIB
                                </p>
                            @endforeach
                        </div>
                    </div>

                    @if ($editable)
                    <div class="organizer-position-actions mt-4 flex flex-wrap items-center gap-3">
                            <a
                                href="{{ route('organizer.events.positions.edit', [$event, $position]) }}"
                                class="organizer-secondary-button organizer-info-edit"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15l-1 6 5-2" /></svg>
                        <span>Edit posisi</span>
                            </a>

                            <form
                                method="POST"
                                action="{{ route('organizer.events.positions.destroy', [$event, $position]) }}"
                                x-data
                                @submit="if (!confirm('Hapus posisi draf ini?')) $event.preventDefault()"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="organizer-secondary-button organizer-position-delete">
                                    Hapus posisi
                                </button>
                            </form>
                    </div>
                    @endif
                </article>
            @empty
                <p class="mt-4 text-slate-600">
                    Belum ada posisi.
                </p>
            @endforelse
        </x-section-card>

        <x-section-card>
            <h2 id="assessment" class="organizer-section-heading text-lg font-semibold">Asesmen</h2>
            @forelse($event->positions as $position)
                @php($assessment = $position->assessments->first())
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-5">
                    <div class="organizer-assessment-summary">
                        <span class="organizer-assessment-symbol" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H6a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3M9 3h6v4H9V3Zm-1 9h8m-8 4h5" /></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="organizer-assessment-title">{{ $position->name }}</p>
                            <p class="organizer-assessment-status">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    @if($assessment?->is_published)
                                        <rect x="5" y="10" width="14" height="11" rx="2" /><path stroke-linecap="round" d="M8 10V7a4 4 0 0 1 8 0v3m-4 4v3" />
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16 4 4 4M4 20l4-1L20 7a2.8 2.8 0 0 0-4-4L4 15l-1 6 5-2" />
                                    @endif
                                </svg>
                                <span>{{ $assessment?->is_published ? 'Dipublikasikan · Terkunci' : ($assessment ? 'Draf' : 'Belum dibuat') }}</span>
                            </p>
                            @if($assessment)
                                <div class="organizer-assessment-meta">
                                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" d="M9 6h12M9 12h12M9 18h12M3 6h1m-1 6h1m-1 6h1" /></svg>{{ $assessment->questions_count }} soal</span>
                                    <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2" /></svg>{{ $assessment->duration_minutes !== null ? $assessment->duration_minutes . ' menit' : 'Durasi belum diatur' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('organizer.assessments.edit', $position) }}" class="organizer-secondary-button organizer-info-edit organizer-assessment-link">{{ $assessment?->is_published || !$editable ? 'Lihat Asesmen' : 'Kelola Asesmen' }}</a>
                </div>
            @empty
                <p class="mt-4 text-sm text-slate-600">Tambahkan posisi di bagian Posisi dan jadwal terlebih dahulu untuk membuat asesmen.</p>
            @endforelse
        </x-section-card>

        @include('organizer.events.coordination')

        <x-section-card id="paket">
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
                    class="organizer-secondary-button organizer-info-edit mt-4"
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

                    <x-organizer-status :status="$order->status" context="Pembayaran" />
                </p>
            @endforeach
        </x-section-card>

        <x-section-card id="tindakan">
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
                        class="organizer-secondary-button organizer-info-edit"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 3h7v7m0-7L10 14m0-11H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-5" /></svg>
                        <span>Lihat halaman publik</span>
                    </a>
                @endif

                @if ($event->publication_status === 'published' || $event->entitlement)
                    <a
                        href="{{ route('organizer.applications.index', $event) }}"
                        class="organizer-secondary-button organizer-info-edit"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m14-14a4 4 0 0 1 0 8m6 6v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" /></svg>
                        <span>Kelola Pelamar Kegiatan</span>
                    </a>
                @endif
            </div>

            @if (in_array($event->lifecycle_status, ['upcoming', 'ongoing']))
                @php($isUnsubmittedDraft = $event->status === 'draft' && !$event->submitted_at && !$event->published_at)
                <form
                    class="organizer-event-cancel mt-6 space-y-3"
                    method="POST"
                    action="{{ route('organizer.events.cancel', $event) }}"
                    x-data="{ confirmation: @js($isUnsubmittedDraft ? 'Batalkan draf ini? Datanya tetap tersimpan.' : 'Batalkan kegiatan beserta proses lamaran terkait?') }"
                    @submit="if (!confirm(confirmation)) $event.preventDefault()"
                >
                    @csrf

                    <h3 class="text-sm font-semibold text-rose-700">{{ $isUnsubmittedDraft ? 'Pembatalan draf' : 'Pembatalan kegiatan' }}</h3>
                    <x-form-field
                        name="reason"
                        label="Alasan pembatalan"
                        required
                        minlength="5"
                        maxlength="1000"
                    />

                    <x-button variant="danger">
                        {{ $isUnsubmittedDraft ? 'Batalkan draf' : 'Batalkan kegiatan' }}
                    </x-button>
                </form>

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
                    @submit="if (!confirm('Hapus draf awal yang belum memiliki posisi ini?')) $event.preventDefault()"
                >
                    @csrf
                    @method('DELETE')

                    <x-button variant="danger">
                        Hapus draf awal
                    </x-button>
                </form>
            @endif
        </x-section-card>

    </div>
    </div>
@endsection
