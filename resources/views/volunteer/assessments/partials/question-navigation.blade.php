<aside class="vaa-navigation min-w-0 lg:order-2" aria-label="Navigasi soal assessment">
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
