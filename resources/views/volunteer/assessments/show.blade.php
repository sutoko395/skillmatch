@extends('volunteer.layouts.sidebar')
@section('title', 'Kerjakan assessment')
@section('content')
<div class="mx-auto max-w-4xl space-y-6" x-data="assessmentAttempt(@js([
    'saveUrl' => route('volunteer.assessments.answers', $attempt),
    'submitUrl' => route('volunteer.assessments.submit', $attempt),
    'returnUrl' => route('volunteer.applications.show', $application),
    'revision' => $attempt->revision, 'expiresAt' => $attempt->expires_at->toIso8601String(),
    'serverNow' => now()->toIso8601String(),
]))">
    <x-page-header title="Assessment" :description="$application->event->title . ' · ' . $application->position->name" />
    <x-section-card>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p>Sisa waktu: <strong x-text="timeLeft">Menghitung…</strong></p>
            <p class="text-sm text-slate-600">Berakhir {{ $attempt->expires_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
        </div>
        <p class="mt-2 text-sm" role="status" aria-live="polite" x-text="message">Jawaban terakhir dimuat dari server.</p>
        <x-input-error :messages="$errors->all()" class="mt-3" />
        <noscript><p class="mt-3 text-rose-700">Aktifkan JavaScript untuk timer dan penyimpanan otomatis. Kamu juga bisa menyimpan atau mengirim melalui tombol di bawah.</p></noscript>
    </x-section-card>
    <form x-ref="answers" method="POST" action="{{ route('volunteer.assessments.submit', $attempt) }}" @change="save()" @submit.prevent="submit()" class="space-y-6">
        @csrf
        <input type="hidden" name="revision" value="{{ $attempt->revision }}" :value="revision" />
        @foreach ($questions as $question)
            <x-section-card>
                <fieldset :disabled="finished">
                    <legend class="whitespace-pre-line font-semibold text-slate-900">{{ $loop->iteration }}. {{ $question->text }}</legend>
                    <div class="mt-4 space-y-3">
                        @foreach ($question->options as $option)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 focus-within:ring-2 focus-within:ring-indigo-500">
                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" @checked(($answers[$question->id] ?? null) == $option->id) class="mt-1 text-indigo-600 focus:ring-indigo-500" />
                                <span class="whitespace-pre-line">{{ $option->label }}. {{ $option->text }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            </x-section-card>
        @endforeach
        <div class="flex flex-wrap gap-4">
            <button type="submit" formaction="{{ route('volunteer.assessments.answers', $attempt) }}" name="_method" value="PUT" @click.prevent="save()" :disabled="busy || finished" class="rounded-xl border border-indigo-600 px-4 py-2 font-semibold text-indigo-700 disabled:opacity-50">Simpan jawaban</button>
            <x-button type="submit" x-bind:disabled="busy || finished">Kirim assessment</x-button>
        </div>
    </form>
</div>
@endsection
