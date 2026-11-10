<div class="vaa-questions min-w-0 space-y-4 lg:order-1">
    <h2 class="font-semibold text-slate-900" aria-live="polite">Soal <span x-text="activeQuestion + 1">1</span> dari {{ $questions->count() }}</h2>
    @foreach ($questions as $question)
        <x-section-card class="vaa-question" x-show="activeQuestion === {{ $loop->index }}" data-question-index="{{ $loop->index }}">
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
