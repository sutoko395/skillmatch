@extends('organizer.layouts.sidebar')
@section('title', 'Kelola Assessment')
@section('content')
<div class="mx-auto max-w-3xl">
    <x-page-header title="Kelola Assessment" :description="$position->event->title . ' · ' . $position->name" />
    <a href="{{ route('organizer.events.show', $position->event) }}#assessment" class="mb-6 inline-block text-sm text-indigo-700 underline">Kembali ke event</a>
    <x-section-card>
        <p class="mb-4 text-sm text-slate-600">{{ $assessment?->is_published ? 'Dipublikasikan · Terkunci' : 'Draft · Belum siap untuk pengajuan event' }}</p>
        @if(!$editable)
            <p class="mb-6 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Assessment dapat dilihat. Soal dan durasi tidak dapat diubah setelah assessment dipublikasikan atau konfigurasi event terkunci.</p>
        @endif
        @php
            $initialQuestions = old('questions', $questions);
            $initialQuestions = array_values(array_map(fn ($row) => [
                'text' => $row['text'] ?? '',
                'options' => array_replace(array_fill_keys(['A', 'B', 'C', 'D'], ''), $row['options'] ?? []),
                'correct' => $row['correct'] ?? '',
            ], $initialQuestions));
        @endphp
        <form method="POST" action="{{ route('organizer.assessments.update', $position) }}" class="space-y-6"
            x-data="{
                busy: false,
                errors: @js($errors->getMessages()),
                questions: @js($initialQuestions),
                nextKey: {{ count($initialQuestions) }},
                init() { this.questions.forEach((q, i) => q.key = i); },
                addQuestion() {
                    this.questions.push({key: this.nextKey++, text: '', options: {A: '', B: '', C: '', D: ''}, correct: ''});
                    this.$nextTick(() => this.$el.querySelectorAll('textarea')[this.questions.length * 5 - 5]?.focus());
                }
            }" @submit="busy = true">
            @csrf
            @method('PUT')
            <input type="hidden" name="revision" value="{{ old('revision', $assessment?->revision ?? 0) }}">
            <fieldset @disabled(!$editable) class="space-y-6">
                <legend class="sr-only">Editor assessment posisi {{ $position->name }}</legend>
                <x-form-field name="duration_minutes" label="Durasi (menit)" type="number" min="1" max="120" :value="$assessment?->duration_minutes" />
                <p class="text-sm text-slate-600">Durasi 1–120 menit. Maksimal 50 soal, masing-masing memiliki empat opsi dan satu kunci jawaban. Draft boleh belum lengkap.</p>
                <p class="font-medium text-slate-900" aria-live="polite"><span x-text="questions.length">{{ count($initialQuestions) }}</span> / 50 soal</p>
                <template x-for="(question, i) in questions" :key="question.key">
                    <fieldset class="space-y-4 rounded-2xl border border-slate-200 p-4 sm:p-6">
                        <legend class="px-2 font-semibold" x-text="'Soal ' + (i + 1)"></legend>
                        <label class="block text-sm font-medium text-slate-700">Pertanyaan
                            <textarea :name="`questions[${i}][text]`" x-model="question.text" maxlength="5000" rows="3" class="mt-2 w-full rounded-xl border-slate-300"></textarea>
                            <span class="mt-1 block text-sm text-rose-700" x-text="(errors['questions.' + i + '.text'] || []).join(' ')"></span>
                        </label>
                        <template x-for="label in ['A', 'B', 'C', 'D']" :key="label">
                            <label class="block text-sm font-medium text-slate-700"><span x-text="'Opsi ' + label"></span>
                                <textarea :name="`questions[${i}][options][${label}]`" x-model="question.options[label]" maxlength="2000" rows="2" class="mt-2 w-full rounded-xl border-slate-300"></textarea>
                                <span class="mt-1 block text-sm text-rose-700" x-text="(errors['questions.' + i + '.options.' + label] || []).join(' ')"></span>
                            </label>
                        </template>
                        <label class="block text-sm font-medium text-slate-700">Kunci jawaban
                            <select :name="`questions[${i}][correct]`" x-model="question.correct" class="mt-2 w-full rounded-xl border-slate-300">
                                <option value="">Pilih satu jawaban benar</option>
                                @foreach(['A', 'B', 'C', 'D'] as $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach
                            </select>
                            <span class="mt-1 block text-sm text-rose-700" x-text="(errors['questions.' + i + '.correct'] || []).join(' ')"></span>
                        </label>
                        @if($editable)
                            <button type="button" @click="questions.splice(i, 1)" class="text-sm font-semibold text-rose-700 underline">Hapus soal</button>
                        @endif
                    </fieldset>
                </template>
                <p x-show="questions.length === 0" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Belum ada soal. Tambahkan soal untuk menyusun assessment.</p>
                @if($editable)
                    <x-button variant="secondary" type="button" @click="addQuestion()" x-bind:disabled="questions.length >= 50 || busy">Tambah soal</x-button>
                @endif
            </fieldset>
            <noscript><p role="alert" class="text-sm text-amber-900">Aktifkan JavaScript untuk mengelola dan menampilkan soal assessment.</p></noscript>
            @if($editable)
                <div class="flex flex-wrap gap-3">
                    <x-button variant="secondary" type="submit" name="action" value="draft">Simpan Draft</x-button>
                    <x-button type="submit" name="action" value="preview">Publikasikan Assessment</x-button>
                </div>
                <p x-show="busy" x-cloak role="status" class="text-sm text-slate-600">Memproses assessment…</p>
            @endif
        </form>
    </x-section-card>
</div>
@endsection
