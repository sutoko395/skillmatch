@extends('organizer.layouts.sidebar')
@section('title', 'Tinjau Lamaran')
@section('content')
<div class="organizer-applicant-page mx-auto max-w-7xl space-y-6">
    <div><a href="{{ route('organizer.applications.index', $application->event) }}" class="organizer-action-button organizer-detail-back">Kembali</a></div>
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Tinjau Lamaran</h1>
        <p class="mt-2 text-sm text-slate-500">{{ $application->event->title }} <span aria-hidden="true">&middot;</span> {{ $application->position->name }}</p>
    </div>

    @php($candidate = $application->snapshot_json['profile'] ?? [])
    <x-section-card>
        <div class="organizer-review-summary">
            <div class="organizer-candidate-identity">
                <span class="organizer-candidate-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($candidate['name'] ?? $application->volunteer->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <h2 class="organizer-review-name">{{ $candidate['name'] ?? $application->volunteer->name }}</h2>
                    <p class="organizer-candidate-meta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg><span>Dikirim {{ $application->submitted_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }} WIB</span></p>
                </div>
            </div>
            <div><p class="mb-2 text-xs text-slate-500">Status seleksi</p><x-application-status :application="$application" /></div>
        </div>

        <div class="organizer-review-evaluation mt-6">
            <x-application-screening :application="$application" :detailed="true" :show-evaluation-info="false" />

        @if ($application->screeningResult?->passed)
            <div>
                <x-application-matching :result="$application->matchResult" :show-evaluation-info="false" />
            </div>
        @endif

        @if (in_array($application->assessmentAttempt?->status, ['submitted', 'expired']))
            <section class="rounded-xl border border-slate-200 bg-slate-50 p-5" aria-label="Hasil assessment">
                <h2 class="text-lg font-bold text-slate-900">Hasil asesmen</h2>
                <p class="mt-2">Nilai: <strong>{{ number_format($application->assessmentAttempt->score, 2, ',', '.') }}/100</strong></p>
                <p class="mt-1 text-sm text-slate-600">{{ $application->assessmentAttempt->correct }} benar dari {{ $application->assessmentAttempt->total }} soal. {{ $application->assessmentAttempt->status === 'expired' ? 'Difinalkan karena waktu habis.' : 'Dikirim peserta.' }}</p>
            </section>
        @endif

        </div>

        <div class="organizer-review-information mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            {{-- Profil Kandidat --}}
            <div class="space-y-4 rounded-xl bg-slate-50 p-5 border border-slate-200">
                <h3 class="text-base font-bold text-gray-900 border-b border-slate-200 pb-2">Informasi Pelamar</h3>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>Nama Lengkap</span>
                    <p class="text-sm font-semibold text-slate-900">{{ $candidate['name'] ?? 'Tidak tercatat dalam snapshot' }}</p>
                </div>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 6 9 7 9-7"/></svg>Email</span>
                    <p class="text-sm text-slate-700">{{ $candidate['email'] ?? 'Tidak tercatat dalam snapshot' }}</p>
                </div>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 3h4l2 5-3 2a16 16 0 0 0 6 6l2-3 5 2v4a2 2 0 0 1-2 2A18 18 0 0 1 3 5a2 2 0 0 1 2-2Z"/></svg>No. Telepon</span>
                    <p class="text-sm text-slate-700">{{ $candidate['phone'] ?? 'Tidak tercatat dalam snapshot' }}</p>
                </div>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>Kota Domisili</span>
                    <p class="text-sm text-slate-700">{{ $candidate['city_name'] ?? (isset($candidate['city_id']) ? 'Kota #'.$candidate['city_id'] : 'Tidak tercatat dalam snapshot') }}@if($candidate['city_province'] ?? null), {{ $candidate['city_province'] }}@endif</p>
                </div>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6M8 13h8M8 17h5"/></svg>Bio Singkat</span>
                    <p class="text-sm text-slate-700">{{ $candidate['bio'] ?? 'Tidak tercatat dalam snapshot' }}</p>
                </div>

                <div class="border-t border-slate-200 pt-3">
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg>Usia saat kegiatan</span>
                    <p class="text-sm font-semibold text-slate-900">{{ $ageAtEvent !== null ? $ageAtEvent.' tahun' : 'Belum tersedia' }}</p>
                    @if($ageAtEvent !== null && $ageFromCurrentProfile)<p class="mt-1 text-xs text-slate-500">Berdasarkan tanggal lahir pada profil saat ini.</p>@endif
                    <x-position-suggested-age :position="$application->position" class="mt-2" />
                </div>

                {{-- Skill Volunteer --}}
                <div class="pt-2">
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 3 6 6 1-4.5 4.5L18 21l-6-3-6 3 1.5-6.5L3 10l6-1Z"/></svg>Keahlian volunteer</span>
                    <div class="mt-1 flex flex-wrap gap-1.5">
                        @forelse(($candidate['skills'] ?? []) as $vs)
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                {{ $vs['name'] ?? 'Keahlian #'.($vs['id'] ?? '?') }} ({{ ucfirst($vs['level'] ?? '') }})
                            </span>
                        @empty
                            <span class="text-xs text-slate-500">Tidak ada keahlian tercatat.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Posisi & Persyaratan --}}
            <div class="space-y-4 rounded-xl bg-slate-50 p-5 border border-slate-200">
                <h3 class="text-base font-bold text-gray-900 border-b border-slate-200 pb-2">Posisi & Kebutuhan</h3>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12a22 22 0 0 0 18 0M12 11v3"/></svg>Posisi</span>
                    <p class="text-sm font-semibold text-slate-900">{{ $application->position->name }}</p>
                </div>
                <div>
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6M8 13h8M8 17h5"/></svg>Deskripsi Tugas</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line">{{ $application->position->description }}</p>
                </div>

                {{-- Skill yang Dibutuhkan Posisi --}}
                <div class="pt-2">
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 3 6 6 1-4.5 4.5L18 21l-6-3-6 3 1.5-6.5L3 10l6-1Z"/></svg>Kebutuhan Keahlian Posisi</span>
                    <ul class="mt-1 list-inside list-disc text-sm text-slate-700">
                        @forelse($application->position->positionSkills as $ps)
                            <li>{{ $ps->skill?->name }} - Min. {{ ucfirst($ps->minimum_level) }} ({{ $ps->is_required ? 'Wajib' : 'Preferensi' }})</li>
                        @empty
                            <li class="text-xs text-slate-500">Tidak ada kualifikasi keahlian khusus.</li>
                        @endforelse
                    </ul>
                </div>

                {{-- Jadwal Posisi --}}
                <div class="pt-2">
                    <span class="organizer-review-field-label"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 11h18"/></svg>Jadwal Tugas</span>
                    <div class="mt-1 space-y-1 text-sm text-slate-700">
                        @forelse($application->position->schedules as $sched)
                            <p>{{ $sched->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $sched->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p>
                        @empty
                            <p class="text-xs text-slate-500">Mengikuti rentang kegiatan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Dokumen Privat Kandidat --}}
        <div class="mt-8 border-t border-slate-200 pt-6">
            <h3 class="text-lg font-bold text-gray-900">Dokumen Pelamar</h3>
            <p class="text-sm text-slate-600">Unduh dokumen berkas resmi yang dilampirkan oleh pelamar.</p>

            <div class="mt-4">
                @if($application->documents->isNotEmpty())
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 font-medium text-slate-600">
                                <tr>
                                    <th class="px-4 py-3">Nama Dokumen</th>
                                    <th class="px-4 py-3">Tipe</th>
                                    <th class="px-4 py-3">Ukuran</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($application->documents as $doc)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-slate-900">
                                            {{ $doc->original_name }}
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $doc->document_type === 'cv' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ strtoupper($doc->document_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ number_format($doc->size_bytes / 1024, 1) }} KB
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-medium text-emerald-700">
                                                Dokumen tersimpan
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($doc->status === 'ready' && auth()->user()->can('download', $doc))
                                            <a href="{{ route('documents.download', $doc) }}" class="organizer-secondary-button organizer-info-edit">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6M8 13h8M8 17h5"/></svg> Unduh dokumen
                                            </a>
                                            @else <span class="text-slate-500">Dokumen tidak tersedia</span> @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                        Pelamar tidak melampirkan berkas dokumen tambahan.
                    </div>
                @endif
            </div>
        </div>
    </x-section-card>
    @if($application->decision_reason || $application->status === 'under_review'
        || ($application->status === 'accepted' && $application->event->lifecycle_status === 'upcoming' && now()->lt($application->event->starts_at)))
        <div class="organizer-applicant-selection"><x-application-selection :application="$application" :show-execution-link="false" /></div>
    @endif
</div>
@endsection
