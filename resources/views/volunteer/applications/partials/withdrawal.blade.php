                <details @if(in_array($status, ['submitted', 'under_review'], true)) open @endif class="vd-withdrawal rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="cursor-pointer rounded-2xl p-5 text-sm font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">Kelola penarikan lamaran</summary>
                    <div class="vd-withdrawal-content">
                        <h2 class="font-semibold text-slate-900">Tarik lamaran</h2>
                        <form method="POST" action="{{ route('volunteer.applications.withdraw', $application) }}" class="mt-3 space-y-4">
                            @csrf
                            <p class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm leading-relaxed text-rose-800">Penarikan bersifat final. Anda tidak dapat melamar ulang pada kegiatan ini.</p>
                            <div>
                                <label for="withdrawal-reason" class="block text-sm font-medium text-slate-700">Alasan penarikan <span class="font-normal text-slate-500">(opsional)</span></label>
                                <textarea id="withdrawal-reason" name="reason" maxlength="1000" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500">{{ old('reason') }}</textarea>
                                @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="volunteer-action volunteer-action--danger rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4h6v2M5 6l1 14h12l1-14M10 10v6m4-6v6" /></svg> Tarik lamaran</button>
                        </form>
                    </div>
                </details>
