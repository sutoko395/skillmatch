                <details @if(in_array($status, ['submitted', 'under_review'], true)) open @endif class="vd-withdrawal rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <summary class="cursor-pointer rounded-2xl p-5 text-sm font-semibold text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">Kelola penarikan lamaran</summary>
                    <div class="vd-withdrawal-content">
                        <h2 class="font-semibold text-slate-900">Tarik lamaran</h2>
                        <form method="POST" action="{{ route('volunteer.applications.withdraw', $application) }}" class="mt-3 space-y-4">
                            @csrf
                            <p class="rounded-xl border border-rose-100 bg-rose-50 p-3 text-sm leading-relaxed text-rose-800">Penarikan bersifat final. Kamu tidak dapat melamar ulang pada event ini.</p>
                            <div>
                                <label for="withdrawal-reason" class="block text-sm font-medium text-slate-700">Alasan penarikan <span class="font-normal text-slate-500">(opsional)</span></label>
                                <textarea id="withdrawal-reason" name="reason" maxlength="1000" rows="2" class="mt-2 block w-full rounded-lg border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500">{{ old('reason') }}</textarea>
                                @error('reason')<p class="mt-2 text-sm text-rose-700">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">Tarik lamaran</button>
                        </form>
                    </div>
                </details>
