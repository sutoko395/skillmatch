@extends('volunteer.layouts.sidebar')
@section('title', 'Kerjakan assessment')
@section('content')
<div class="mx-auto max-w-6xl space-y-6" x-data="assessmentAttempt(@js([
    'saveUrl' => route('volunteer.assessments.answers', $attempt),
    'submitUrl' => route('volunteer.assessments.submit', $attempt),
    'returnUrl' => route('volunteer.applications.show', $application),
    'revision' => $attempt->revision, 'expiresAt' => $attempt->expires_at->toIso8601String(),
    'serverNow' => now()->toIso8601String(),
    'questionIds' => $questions->pluck('id')->values()->all(),
    'answeredIds' => array_keys(array_filter($answers, fn ($answer) => $answer !== null)),
]))">
    <x-page-header title="Assessment" :description="$application->event->title . ' · ' . $application->position->name" />
    <x-section-card class="sticky top-20 z-20">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p>Sisa waktu: <strong x-text="timeLeft">Menghitung…</strong></p>
            <p class="text-sm text-slate-600">Berakhir {{ $attempt->expires_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
        </div>
        <p class="mt-2 text-sm text-slate-600">Terjawab <strong x-text="answeredCount">{{ count(array_filter($answers, fn ($answer) => $answer !== null)) }}</strong> dari {{ $questions->count() }} soal.</p>
        <p class="mt-2 text-sm" role="status" aria-live="polite" x-text="message">Jawaban terakhir dimuat dari server.</p>
        <x-input-error :messages="$errors->all()" class="mt-3" />
        <noscript><p class="mt-3 text-rose-700">Aktifkan JavaScript untuk timer dan penyimpanan otomatis. Kamu juga bisa menyimpan atau mengirim melalui tombol di bawah.</p></noscript>
    </x-section-card>
    <form x-ref="answers" method="POST" action="{{ route('volunteer.assessments.submit', $attempt) }}" @change="answerChanged()" @submit.prevent="submit()" class="space-y-6">
        @csrf
        <input type="hidden" name="revision" value="{{ $attempt->revision }}" :value="revision" />
        <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_15rem]">
            <aside class="min-w-0 lg:order-2" aria-label="Navigasi soal assessment">
                <button type="button" @click="listOpen = !listOpen" :aria-expanded="listOpen" aria-controls="volunteer-question-list" class="flex w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden">
                    <span>Daftar soal ({{ $questions->count() }})</span>
                    <span aria-hidden="true" x-text="listOpen ? '−' : '+'">+</span>
                </button>
                <div id="volunteer-question-list" :class="listOpen ? 'block' : 'hidden lg:block'" class="mt-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm lg:mt-0">
                    <h2 class="font-semibold text-slate-900">Daftar soal</h2>
                    <div class="mt-3 grid max-h-64 grid-cols-5 gap-2 overflow-y-auto p-1" aria-label="Nomor soal">
                        @foreach ($questions as $question)
                            <button type="button" @click="selectQuestion({{ $loop->index }})" :disabled="finished" :aria-current="activeQuestion === {{ $loop->index }} ? 'step' : null" :aria-label="'Soal {{ $loop->iteration }}' + (questionAnswered({{ $question->id }}) ? ', sudah dijawab' : ', belum dijawab')" :class="[questionAnswered({{ $question->id }}) ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-slate-300 bg-white text-slate-700', activeQuestion === {{ $loop->index }} ? 'ring-2 ring-indigo-600 ring-offset-1' : '']" class="min-h-10 rounded-lg border text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:opacity-50">{{ $loop->iteration }}</button>
                        @endforeach
                    </div>
                    <ul class="mt-4 space-y-2 text-xs text-slate-600">
                        <li class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-emerald-300 bg-emerald-50" aria-hidden="true"></span>Sudah dijawab</li>
                        <li class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-slate-300 bg-white" aria-hidden="true"></span>Belum dijawab</li>
                    </ul>
                    <p class="mt-3 text-xs text-slate-600">Kamu bisa kembali ke soal lain sebelum mengirim assessment.</p>
                </div>
            </aside>
            <div class="min-w-0 space-y-4 lg:order-1">
                <h2 class="font-semibold text-slate-900" aria-live="polite">Soal <span x-text="activeQuestion + 1">1</span> dari {{ $questions->count() }}</h2>
                @foreach ($questions as $question)
                    <x-section-card x-show="activeQuestion === {{ $loop->index }}" data-question-index="{{ $loop->index }}">
                        <fieldset :disabled="finished">
                            <legend tabindex="-1" class="scroll-mt-64 whitespace-pre-line font-semibold text-slate-900 focus:outline-none">{{ $loop->iteration }}. {{ $question->text }}</legend>
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
                <div class="flex items-center justify-between gap-3">
                    <x-button variant="secondary" type="button" @click="selectQuestion(activeQuestion - 1)" x-bind:disabled="activeQuestion === 0 || finished">Sebelumnya</x-button>
                    <x-button variant="secondary" type="button" @click="selectQuestion(activeQuestion + 1)" x-bind:disabled="activeQuestion >= questionIds.length - 1 || finished">Berikutnya</x-button>
                </div>
            </div>
        </div>
        <div class="sticky bottom-3 z-20 flex flex-wrap items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <button type="submit" formaction="{{ route('volunteer.assessments.answers', $attempt) }}" name="_method" value="PUT" @click.prevent="save()" :disabled="busy || finished" class="rounded-xl border border-indigo-600 px-4 py-2 font-semibold text-indigo-700 disabled:opacity-50">Simpan jawaban</button>
            <x-button type="submit" x-bind:disabled="busy || finished">Kirim assessment</x-button>
        </div>
    </form>
</div>
@endsection
