@props(['application', 'detailed' => false])
@if ($application->status !== 'draft')
    @php($result = $application->screeningResult)
    <section class="rounded-xl border border-slate-200 bg-white p-5" aria-label="Hasil screening">
        <h2 class="text-lg font-bold text-slate-900">Hasil screening</h2>
        @if (!$result)
            <p class="mt-3 text-sm text-slate-600">
                {{ $application->status === 'submitted' ? 'Lamaran menunggu evaluasi. Hasil screening belum tersedia.' : 'Hasil screening belum tersedia untuk lamaran ini.' }}
            </p>
        @else
            <div class="mt-3 rounded-lg p-4 text-sm {{ $result->passed ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">
                <p class="font-semibold">{{ $result->passed ? 'Lolos screening — syarat wajib terpenuhi.' : 'Gagal screening — belum memenuhi syarat wajib.' }}</p>
                @if (!$result->passed)
                    <p class="mt-1">{{ $detailed ? 'Pelamar belum memenuhi syarat untuk melanjutkan proses.' : 'Lamaranmu belum bisa dilanjutkan ke tahap selanjutnya.' }}</p>
                @elseif ($application->status === 'submitted')
                    <p class="mt-1">{{ $detailed ? 'Pelamar menunggu proses pencocokan sebelum assessment.' : 'Lamaranmu menunggu proses pencocokan sebelum assessment.' }}</p>
                @elseif ($application->status === 'assessment_pending')
                    <p class="mt-1">{{ $detailed ? 'Pencocokan selesai. Pelamar menunggu assessment.' : 'Pencocokan selesai. Lamaranmu siap lanjut ke assessment.' }}</p>
                @endif
            </div>
            @if ($detailed)
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <caption class="sr-only">Rincian syarat screening berdasarkan data saat submit</caption>
                        <thead class="bg-slate-50 text-slate-600"><tr>
                            <th scope="col" class="px-3 py-3">Syarat</th>
                            <th scope="col" class="px-3 py-3">Hasil</th>
                            <th scope="col" class="px-3 py-3">Keterangan</th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($result->checks_json as $check)
                                <tr>
                                    <th scope="row" class="px-3 py-3 font-medium text-slate-900">{{ $check['label'] }}</th>
                                    <td class="px-3 py-3 {{ !$check['required'] ? 'text-slate-600' : ($check['passed'] ? 'text-emerald-700' : 'text-rose-700') }}">
                                        {{ ($check['manual'] ?? false) ? 'Ditinjau manual' : (!$check['required'] ? 'Tidak diwajibkan' : ($check['passed'] ? 'Terpenuhi' : 'Tidak terpenuhi')) }}
                                    </td>
                                    <td class="px-3 py-3 text-slate-700">{{ $check['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif (!$result->passed)
                <h3 class="mt-4 text-sm font-semibold text-slate-900">Alasan</h3>
                <ul class="mt-2 space-y-2 text-sm text-rose-800">
                    @foreach ($result->checks_json as $check)
                        @if ($check['required'] && !$check['passed'])
                            <li class="rounded-lg bg-rose-50 p-3"><span class="font-semibold">{{ $check['label'] }}:</span> {{ $check['reason'] }}</li>
                        @endif
                    @endforeach
                </ul>
                <p class="mt-3 text-sm text-slate-600">Lamaran ini tidak dapat dilanjutkan atau dikirim ulang pada event yang sama.</p>
            @endif
            <p class="mt-4 text-xs text-slate-500">Berdasarkan data saat lamaran dikirim. Dievaluasi {{ $result->evaluated_at->copy()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB.</p>
        @endif
    </section>
@endif
