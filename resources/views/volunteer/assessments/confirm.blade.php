@extends('volunteer.layouts.sidebar')
@section('title', 'Mulai assessment')
@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <x-page-header title="Mulai assessment" :description="$application->event->title . ' · ' . $application->position->name" />
    <x-section-card>
        <p>{{ $assessment->questions_count }} soal · Durasi maksimal {{ $assessment->duration_minutes }} menit.</p>
        <p class="mt-3">Jika dimulai sekarang, pengerjaan berakhir paling lambat <strong>{{ $expires_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</strong>.</p>
        <p class="mt-3">Waktu mulai berjalan setelah kamu menekan “Mulai sekarang” dan tetap berjalan jika halaman ditutup. Waktu pengerjaan mengikuti batas yang lebih dulu tercapai: durasi atau tenggat assessment. Kamu hanya dapat mengerjakan satu kali.</p>
        <x-input-error :messages="$errors->all()" class="mt-3" />
        <form method="POST" action="{{ route('volunteer.assessments.start', $application) }}" class="mt-5 flex flex-wrap items-center gap-4">
            @csrf
            <input type="hidden" name="confirmed" value="1" />
            <x-button type="submit">Mulai sekarang</x-button>
            <a class="font-semibold text-indigo-600 underline" href="{{ route('volunteer.applications.show', $application) }}">Kembali ke lamaran</a>
        </form>
    </x-section-card>
</div>
@endsection
