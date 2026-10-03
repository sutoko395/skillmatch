@extends('admin.layouts.sidebar')

@section('title', 'Periksa Event')

@section('content')
<div class="w-full max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Periksa Event
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Periksa informasi event sebelum memberikan keputusan.
            </p>
        </div>

        <a
            href="{{ route('admin.event-verification.index') }}"
            class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
        >
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="inline-flex px-2.5 py-1 rounded-lg bg-amber-50 text-amber-600 text-xs font-bold">
                            Menunggu Verifikasi
                        </span>

                        <h2 class="mt-4 text-xl font-bold text-slate-800">
                            {{ $event->title }}
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ $event->description }}
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-6">

                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            Kategori
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $event->category?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            Organizer
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $event->organizer?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            Lokasi
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $event->location }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            Pendaftaran Ditutup
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ optional($event->registration_deadline)->format('d M Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            Mulai Event
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ optional($event->start_date)->format('d M Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-slate-400">
                            Selesai Event
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ optional($event->end_date)->format('d M Y') }}
                        </p>
                    </div>

                </div>

            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

                <div class="px-6 py-5 border-b border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800">
                        Posisi Volunteer
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Daftar posisi dan kebutuhan volunteer dalam event.
                    </p>
                </div>

                <div class="divide-y divide-slate-100">

                    @forelse($event->positions as $position)
                        <div class="p-6">

                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">

                                <div>
                                    <h3 class="font-bold text-slate-800">
                                        {{ $position->position_name }}
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Kuota {{ $position->quota }} volunteer
                                    </p>
                                </div>

                                <span class="inline-flex w-fit px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-xs font-bold">
                                    {{ $position->min_skill_level }}
                                </span>

                            </div>

                            @if($position->skills->count())
                                <div class="mt-4">
                                    <p class="text-xs font-semibold text-slate-400 mb-2">
                                        Skill Dibutuhkan
                                    </p>

                                    <div class="flex flex-wrap gap-2">
                                        @foreach($position->skills as $skill)
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                                                {{ $skill->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

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
                            Belum ada posisi volunteer.
                        </div>
                    @endforelse

                </div>

            </div>

        </div>

        <div class="space-y-6">

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">

                <h2 class="text-lg font-bold text-slate-800">
                    Keputusan Verifikasi
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Pilih tindakan untuk event ini.
                </p>

                <div class="mt-6 space-y-3">

                    <form
                        action="{{ route('admin.event-verification.approve', $event) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            onclick="return confirm('Setujui event ini?')"
                            class="w-full px-4 py-3 rounded-xl bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition"
                        >
                            Setujui Event
                        </button>
                    </form>

                    <button
                        type="button"
                        onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                        class="w-full px-4 py-3 rounded-xl bg-red-50 text-red-600 text-sm font-semibold hover:bg-red-100 transition"
                    >
                        Tolak Event
                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<div
    id="reject-modal"
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
                        Tolak Event
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
                    type="button"
                    onclick="document.getElementById('reject-modal').classList.add('hidden')"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-semibold hover:bg-slate-50 transition"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-4 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition"
                >
                    Tolak Event
                </button>

            </div>
        </form>

    </div>
</div>
@endsection