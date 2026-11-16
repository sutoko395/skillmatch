<div class="vaa-actions sticky bottom-3 z-20 flex flex-wrap items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
    <button type="submit" formaction="{{ route('volunteer.assessments.answers', $attempt) }}" name="_method" value="PUT" @click.prevent="save()" :disabled="busy || finished" class="volunteer-action volunteer-action--blue rounded-xl border border-indigo-600 px-4 py-2 font-semibold text-indigo-700 disabled:opacity-50"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" /></svg> Simpan jawaban</button>
    <x-button type="submit" x-bind:disabled="busy || finished">Kirim asesmen</x-button>
</div>
