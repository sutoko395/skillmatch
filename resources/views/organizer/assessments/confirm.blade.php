@extends('organizer.layouts.sidebar')
@section('title', 'Konfirmasi Publikasi Assessment')
@section('content')
<div class="mx-auto max-w-3xl">
    <x-page-header title="Konfirmasi Publikasi Assessment" :description="$position->event->title" />
    <x-section-card>
        <dl class="space-y-3 text-slate-900">
            <div><dt class="text-sm text-slate-600">Posisi</dt><dd class="font-semibold">{{ $position->name }}</dd></div>
            <div><dt class="text-sm text-slate-600">Jumlah soal</dt><dd>{{ count($data['questions']) }} soal</dd></div>
            <div><dt class="text-sm text-slate-600">Durasi</dt><dd>{{ $data['duration_minutes'] }} menit</dd></div>
        </dl>
        <p class="my-6 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Setelah dipublikasikan, soal, opsi, kunci jawaban, dan durasi dikunci. Publikasi assessment menyatakan soal siap dipakai; event tetap mengikuti proses pengajuan dan publikasinya.</p>
        <form method="POST" action="{{ route('organizer.assessments.update', $position) }}" x-data="{busy: false}" @submit="busy = true" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="revision" value="{{ $data['revision'] }}">
            <input type="hidden" name="duration_minutes" value="{{ $data['duration_minutes'] }}">
            @foreach($data['questions'] as $index => $question)
                <input type="hidden" name="questions[{{ $index }}][text]" value="{{ $question['text'] }}">
                <input type="hidden" name="questions[{{ $index }}][correct]" value="{{ $question['correct'] }}">
                @foreach(['A', 'B', 'C', 'D'] as $label)
                    <input type="hidden" name="questions[{{ $index }}][options][{{ $label }}]" value="{{ $question['options'][$label] }}">
                @endforeach
            @endforeach
            <label class="flex items-start gap-3 text-sm text-slate-700"><input type="checkbox" name="confirmed" value="1" class="mt-1 text-indigo-600">Saya sudah memeriksa assessment dan memahami bahwa isinya akan dikunci.</label>
            <div class="flex flex-wrap gap-3">
                <x-button variant="secondary" type="submit" name="action" value="draft">Simpan draft dan kembali</x-button>
                <x-button name="action" value="publish">Konfirmasi Publikasi</x-button>
            </div>
        </form>
    </x-section-card>
</div>
@endsection
