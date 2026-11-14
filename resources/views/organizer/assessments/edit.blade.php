@extends('organizer.layouts.sidebar')
@section('title', 'Kelola Asesmen')
@section('content')
<div class="mx-auto max-w-6xl">
    <x-page-header title="Kelola Asesmen" :description="$position->event->title . ' · ' . $position->name" />
    <a href="{{ route('organizer.events.show', $position->event) }}#assessment" class="organizer-action-button organizer-detail-back mb-6">Kembali ke kegiatan</a>
    <x-section-card>
        <p class="mb-4 text-sm text-slate-600">{{ $assessment?->is_published ? 'Dipublikasikan · Terkunci' : 'Draf · Belum siap untuk pengajuan kegiatan' }}</p>
        @if(!$editable)
            <p class="mb-6 rounded-xl bg-amber-50 p-4 text-sm text-amber-900">Asesmen dapat dilihat. Soal dan durasi tidak dapat diubah setelah asesmen dipublikasikan atau konfigurasi kegiatan terkunci.</p>
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
                activeQuestion: 0,
                listOpen: false,
                init() {
                    this.questions.forEach((q, i) => q.key = i);
                    const firstError = this.questions.findIndex((q, i) => this.hasError(i));
                    if (firstError >= 0) this.activeQuestion = firstError;
                },
                isComplete(question) {
                    return Boolean(String(question.text ?? '').trim() && ['A', 'B', 'C', 'D'].every(label => String(question.options[label] ?? '').trim()) && ['A', 'B', 'C', 'D'].includes(question.correct));
                },
                hasError(index) {
                    return Object.keys(this.errors).some(key => key.startsWith('questions.' + index + '.'));
                },
                selectQuestion(index) {
                    if (index < 0 || index >= this.questions.length) return;
                    this.activeQuestion = index;
                    this.listOpen = false;
                    this.$nextTick(() => this.$el.querySelector('[data-question-index=&quot;' + index + '&quot;] legend')?.focus());
                },
                addQuestion() {
                    if (this.questions.length >= 50 || this.busy) return;
                    this.questions.push({key: this.nextKey++, text: '', options: {A: '', B: '', C: '', D: ''}, correct: ''});
                    this.selectQuestion(this.questions.length - 1);
                    this.$nextTick(() => this.$el.querySelector('[data-question-index=&quot;' + this.activeQuestion + '&quot;] textarea')?.focus());
                },
                removeQuestion(index) {
                    this.questions.splice(index, 1);
                    const remapped = {};
                    Object.entries(this.errors).forEach(([key, messages]) => {
                        const match = key.match(/^questions\.(\d+)\.(.+)$/);
                        if (!match) remapped[key] = messages;
                        else if (Number(match[1]) !== index) remapped['questions.' + (Number(match[1]) > index ? Number(match[1]) - 1 : Number(match[1])) + '.' + match[2]] = messages;
                    });
                    this.errors = remapped;
                    this.activeQuestion = Math.max(0, Math.min(index, this.questions.length - 1));
                    if (this.questions.length) this.selectQuestion(this.activeQuestion);
                    else this.$nextTick(() => this.$refs.addQuestion?.focus());
                }
            }" @submit="busy = true">
            @csrf
            @method('PUT')
            <input type="hidden" name="revision" value="{{ old('revision', $assessment?->revision ?? 0) }}">
            <fieldset class="space-y-6">
                <legend class="sr-only">Editor assessment posisi {{ $position->name }}</legend>
                <div class="grid items-end gap-4 sm:grid-cols-2">
                    <x-form-field name="duration_minutes" label="Durasi (menit)" type="number" min="1" max="120" :value="$assessment?->duration_minutes" :disabled="!$editable" />
                    <div class="rounded-xl bg-slate-50 p-4 text-sm">
                        <p class="font-semibold text-slate-900"><span x-text="questions.length">{{ count($initialQuestions) }}</span> / 50 soal</p>
                        <p class="mt-1 text-slate-600"><span x-text="questions.filter(question => isComplete(question)).length">0</span> lengkap · <span x-text="questions.filter(question => !isComplete(question)).length">{{ count($initialQuestions) }}</span> belum lengkap</p>
                    </div>
                </div>
                <p class="text-sm text-slate-600">Durasi 1–120 menit. Maksimal 50 soal, masing-masing memiliki empat opsi dan satu kunci jawaban. Draf boleh belum lengkap.</p>
                <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_15rem]">
                    <aside class="min-w-0 lg:order-2" aria-label="Navigasi soal">
                        <button type="button" @click="listOpen = !listOpen" :aria-expanded="listOpen" aria-controls="assessment-question-list" class="flex w-full items-center justify-between rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden">
                            <span>Daftar soal (<span x-text="questions.length">{{ count($initialQuestions) }}</span>)</span>
                            <span aria-hidden="true" x-text="listOpen ? '−' : '+'">+</span>
                        </button>
                        <div id="assessment-question-list" :class="listOpen ? 'block' : 'hidden lg:block'" class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4 lg:sticky lg:top-6 lg:mt-0">
                            <h2 class="font-semibold text-slate-900">Daftar soal</h2>
                            <div class="mt-3 grid max-h-64 grid-cols-5 gap-2 overflow-y-auto p-1" aria-label="Nomor soal">
                                <template x-for="(question, i) in questions" :key="question.key">
                                    <button type="button" @click="selectQuestion(i)" :aria-current="activeQuestion === i ? 'step' : null" :aria-label="'Soal ' + (i + 1) + (hasError(i) ? ', perlu diperiksa' : (isComplete(question) ? ', lengkap' : ', belum lengkap'))" :class="[hasError(i) ? 'border-rose-300 bg-rose-50 text-rose-700' : (isComplete(question) ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-slate-300 bg-white text-slate-700'), activeQuestion === i ? 'ring-2 ring-indigo-600 ring-offset-1' : '']" class="min-h-10 rounded-lg border text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1" x-text="i + 1"></button>
                                </template>
                            </div>
                            <ul class="mt-4 space-y-2 text-xs text-slate-600">
                                <li class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-emerald-300 bg-emerald-50" aria-hidden="true"></span>Lengkap</li>
                                <li class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-slate-300 bg-white" aria-hidden="true"></span>Belum lengkap</li>
                                <li x-show="Object.keys(errors).some(key => key.startsWith('questions.'))" class="flex items-center gap-2"><span class="h-3 w-3 rounded border border-rose-300 bg-rose-50" aria-hidden="true"></span>Perlu diperiksa</li>
                            </ul>
                            <p class="mt-3 text-xs text-slate-600">Klik nomor untuk berpindah soal. Simpan draf untuk menyimpan semua perubahan.</p>
                        </div>
                    </aside>
                    <div class="min-w-0 space-y-4 lg:order-1">
                        <div x-show="questions.length" class="flex flex-wrap items-center justify-between gap-3">
                            <h2 class="font-semibold text-slate-900" aria-live="polite">Soal <span x-text="activeQuestion + 1">1</span> dari <span x-text="questions.length">{{ count($initialQuestions) }}</span></h2>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600" x-text="questions[activeQuestion] && isComplete(questions[activeQuestion]) ? 'Lengkap' : 'Belum lengkap'"></span>
                        </div>
                        <template x-for="(question, i) in questions" :key="question.key">
                            <fieldset x-show="activeQuestion === i" :data-question-index="i" @disabled(!$editable) class="space-y-4 rounded-2xl border border-slate-200 p-4 sm:p-6">
                                <legend tabindex="-1" class="px-2 font-semibold focus:outline-none" x-text="'Soal ' + (i + 1)"></legend>
                                <label class="block text-sm font-medium text-slate-700">Pertanyaan
                                    <textarea :name="`questions[${i}][text]`" x-model="question.text" maxlength="5000" rows="3" class="mt-2 w-full rounded-xl border-slate-300"></textarea>
                                    <span class="mt-1 block text-sm text-rose-700" x-text="(errors['questions.' + i + '.text'] || []).join(' ')"></span>
                                </label>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <template x-for="label in ['A', 'B', 'C', 'D']" :key="label">
                                        <label class="block text-sm font-medium text-slate-700"><span x-text="'Opsi ' + label"></span>
                                            <textarea :name="`questions[${i}][options][${label}]`" x-model="question.options[label]" maxlength="2000" rows="2" class="mt-2 w-full rounded-xl border-slate-300"></textarea>
                                            <span class="mt-1 block text-sm text-rose-700" x-text="(errors['questions.' + i + '.options.' + label] || []).join(' ')"></span>
                                        </label>
                                    </template>
                                </div>
                                <label class="block text-sm font-medium text-slate-700">Kunci jawaban
                                    <select :name="`questions[${i}][correct]`" x-model="question.correct" class="mt-2 w-full rounded-xl border-slate-300">
                                        <option value="">Pilih satu jawaban benar</option>
                                        @foreach(['A', 'B', 'C', 'D'] as $label)<option value="{{ $label }}">{{ $label }}</option>@endforeach
                                    </select>
                                    <span class="mt-1 block text-sm text-rose-700" x-text="(errors['questions.' + i + '.correct'] || []).join(' ')"></span>
                                </label>
                                @if($editable)
                                    <button type="button" @click="removeQuestion(i)" :disabled="busy" class="organizer-secondary-button organizer-position-delete disabled:opacity-50">Hapus soal</button>
                                @endif
                            </fieldset>
                        </template>
                        <p x-show="questions.length === 0" class="rounded-xl bg-slate-50 p-4 text-sm text-slate-600">Belum ada soal. Tambahkan soal untuk menyusun assessment.</p>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 pt-4">
                            <div x-show="questions.length" class="flex gap-2">
                                <x-button variant="secondary" type="button" @click="selectQuestion(activeQuestion - 1)" x-bind:disabled="activeQuestion === 0 || busy">Sebelumnya</x-button>
                                <x-button variant="secondary" type="button" @click="selectQuestion(activeQuestion + 1)" x-bind:disabled="activeQuestion >= questions.length - 1 || busy">Berikutnya</x-button>
                            </div>
                            @if($editable)
                                <button type="button" x-ref="addQuestion" @click="addQuestion()" :disabled="questions.length >= 50 || busy" class="organizer-secondary-button organizer-info-edit disabled:opacity-50">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                    <span>Tambah soal</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </fieldset>
            <noscript><p role="alert" class="text-sm text-amber-900">Aktifkan JavaScript untuk mengelola dan menampilkan soal assessment.</p></noscript>
            @if($editable)
                <div class="sticky bottom-3 z-10 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <x-button variant="secondary" type="submit" name="action" value="draft">Simpan Draft</x-button>
                    <x-button type="submit" name="action" value="preview">Publikasikan Asesmen</x-button>
                    <p class="text-xs text-slate-500">Menyimpan seluruh soal dalam assessment.</p>
                </div>
                <p x-show="busy" x-cloak role="status" class="text-sm text-slate-600">Memproses assessment…</p>
            @endif
        </form>
    </x-section-card>
</div>
@endsection
