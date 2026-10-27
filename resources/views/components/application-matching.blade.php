@props(['result'])
<section class="rounded-xl border border-slate-200 bg-white p-5" aria-label="Kecocokan kandidat">
    <h2 class="text-lg font-bold text-slate-900">Kecocokan kandidat</h2>
    @if (!$result)
        <p class="mt-3 text-sm text-slate-600">Hasil matching belum tersedia.</p>
    @else
        <p class="mt-3 text-3xl font-bold text-indigo-700">{{ number_format($result->total_score, 2, ',', '.') }} <span class="text-base font-normal text-slate-500">/ 100</span></p>
        <p class="text-sm text-slate-600">Match Score</p>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <caption class="sr-only">Skor kecocokan dan kontribusi tiap komponen</caption>
                <thead class="bg-slate-50 text-slate-600"><tr>
                    <th scope="col" class="px-3 py-3">Komponen</th>
                    <th scope="col" class="px-3 py-3">Skor</th>
                    <th scope="col" class="px-3 py-3">Bobot</th>
                    <th scope="col" class="px-3 py-3">Poin</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ([['Skill', $result->skill_score, 50], ['Ketersediaan', $result->availability_score, 30], ['Lokasi', $result->location_score, 20]] as [$label, $score, $weight])
                        <tr>
                            <th scope="row" class="px-3 py-3 font-medium text-slate-900">{{ $label }}</th>
                            <td class="px-3 py-3">{{ number_format($score, 2, ',', '.') }}</td>
                            <td class="px-3 py-3">{{ $weight }}%</td>
                            <td class="px-3 py-3">{{ number_format($score * $weight / 100, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <ul class="mt-4 space-y-1 text-sm text-slate-700">
            <li>Skill: {{ $result->breakdown_json['skills']['matched'] }} dari {{ $result->breakdown_json['skills']['total'] }} memenuhi level minimum.</li>
            <li>Jadwal: {{ number_format($result->breakdown_json['availability']['covered_seconds'] / 3600, 2, ',', '.') }} dari {{ number_format($result->breakdown_json['availability']['total_seconds'] / 3600, 2, ',', '.') }} jam tercakup.</li>
            <li>Lokasi: {{ $result->breakdown_json['location']['same_city'] ? 'kota pelamar sama dengan kota event.' : 'kota pelamar berbeda dari kota event.' }}</li>
        </ul>
        <p class="mt-4 text-xs text-slate-500">Dihitung dari data saat melamar. Nilai assessment terpisah; keputusan penerimaan tetap oleh EO.</p>
    @endif
</section>
