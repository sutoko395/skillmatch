@extends('admin.layouts.sidebar')

@section('title', 'Periksa Kegiatan')

@section('content')
<div class="admin-event-review w-full max-w-7xl mx-auto space-y-6">

    <div><a href="{{ route('admin.event-verification.index') }}" class="admin-action admin-action-back">Kembali</a></div>
    <x-page-header title="Periksa Kegiatan" description="Tinjau informasi kegiatan dan kelengkapan pengajuan." />
    <nav class="flex flex-wrap gap-2 lg:hidden" aria-label="Pintasan pemeriksaan">
        <a href="#verification-decision" class="admin-action admin-action-blue">Keputusan verifikasi</a>
        @if($event->verification_note || $event->verified_at)<a href="#verification-note" class="admin-action admin-action-blue">Catatan terakhir</a>@endif
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            @include('admin.event-verification.summary')

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800">
                        Posisi relawan
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Tugas, jadwal, dan kebutuhan relawan.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">

                    @forelse($event->positions as $position)
                        <div class="p-6">

                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                                <div>
                                    <h3 class="font-bold text-slate-800">
                                        {{ $position->name }}
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Kuota {{ $position->quota }} relawan
                                    </p>
                                </div>

                                <span class="inline-flex w-fit px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold">
                                    Level mengikuti setiap keahlian
                                </span>

                            </div>

                            @if($position->description)<p class="mt-3 whitespace-pre-line text-sm text-slate-600">{{ $position->description }}</p>@endif
                            <x-position-suggested-age :position="$position" class="mt-2" />
                            @if($position->positionSkills->count())
                                <div class="mt-4">
                                    <p class="text-xs font-semibold text-slate-400 mb-2">
                                        Keahlian yang dibutuhkan
                                    </p>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach($position->positionSkills as $skill)
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                                {{ $skill->skill->name }} / {{ $skill->minimum_level }} ({{ $skill->is_required ? 'wajib' : 'preferensi' }})
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @foreach($position->schedules as $schedule)
                                <p class="mt-3 text-sm text-slate-600">{{ $schedule->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $schedule->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                            @endforeach
                            @if($position->requirements->count())
                                <div class="mt-4">
                                    <p class="text-xs font-semibold text-slate-400 mb-2">
                                        Persyaratan
                                    </p>

                                    <div class="space-y-2">
                                        @foreach($position->requirements as $requirement)
                                            <div class="text-sm text-slate-600">
                                                <span class="font-semibold text-slate-700">
                                                    {{ $requirement->name }}
                                                </span>

                                                @if($requirement->description)
                                                    <span>
                                                        — {{ $requirement->description }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-slate-500">
                            Belum ada posisi relawan.
                        </div>
                    @endforelse

                </div>

            </div>

        </div>

        <div class="space-y-6">
            @include('admin.event-verification.context')

            <div id="verification-decision" style="scroll-margin-top:96px" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

                <h2 class="text-lg font-bold text-slate-800">
                    Keputusan Verifikasi
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Pilih tindakan untuk kegiatan ini.
                </p>

                @if($event->status === 'pending')
                <div class="mt-6 space-y-3">

                    <form
                        action="{{ route('admin.event-verification.approve', $event) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            onclick="return confirm('Setujui kegiatan ini?')"
                            class="w-full px-4 py-3 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition"
                        >
                            Setujui Kegiatan
                        </button>
                    </form>

                    <button
                        type="button"
                        onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                        class="admin-action admin-action-danger w-full px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm font-semibold hover:bg-red-100 transition"
                    >
                        Tolak Kegiatan
                    </button>

                </div>
                @else
                    <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">Keputusan sudah diberikan. Pengajuan harus diajukan ulang sebelum diperiksa kembali.</p>
                @endif

            </div>

        </div>

    </div>

</div>

<div
    id="reject-modal"
    x-data @keydown.escape.window="document.getElementById('reject-modal').classList.add('hidden')"
    class="hidden fixed inset-0 z-[9999] flex h-screen w-screen items-center justify-center p-4"
>
    <div
        class="fixed inset-0 h-screen w-screen bg-slate-900/50 backdrop-blur-sm"
        onclick="document.getElementById('reject-modal').classList.add('hidden')"
    ></div>

    <div class="relative z-10 w-full max-w-lg bg-white rounded-2xl shadow-2xl">

        <div class="px-6 py-5 border-b border-slate-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">
                        Tolak Kegiatan
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Berikan alasan agar Organizer mengetahui bagian yang perlu diperbaiki.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="document.getElementById('reject-modal').classList.add('hidden')"
                    class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                >
                    ✕
                </button>
            </div>
        </div>

        <form
            action="{{ route('admin.event-verification.reject', $event) }}"
            method="POST"
        >
            @csrf
            @method('PATCH')

            <div class="p-6">
                <label class="block text-sm font-semibold text-slate-700">
                    Alasan Penolakan
                </label>

                <textarea
                    name="verification_note"
                    rows="5"
                    required
                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Tuliskan alasan penolakan..."
                ></textarea>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">

                <button
                    type="submit"
                    class="admin-action admin-action-danger px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition"
                >
                    Tolak Kegiatan
                </button>

                <button
                    type="button"
                    onclick="document.getElementById('reject-modal').classList.add('hidden')"
                    class="admin-action admin-action-danger px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                >
                    Batal
                </button>

            </div>
        </form>

    </div>
</div>
@endsection
