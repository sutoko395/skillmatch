    <x-section-card class="vd-attendance order-1">
        <h2 class="text-lg font-bold text-slate-900">Kehadiran</h2>
        <p class="mt-4"><span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ ($application->attendance?->status ?? 'unrecorded') === 'present' ? 'bg-emerald-50 text-emerald-800' : (($application->attendance?->status ?? 'unrecorded') === 'absent' ? 'bg-rose-50 text-rose-800' : 'bg-slate-100 text-slate-700') }}">{{ ['unrecorded' => 'Belum dicatat', 'present' => 'Hadir', 'absent' => 'Tidak hadir'][$application->attendance?->status ?? 'unrecorded'] }}</span></p>
        <p class="mt-3 text-sm text-slate-600">Kehadiran dicatat oleh penyelenggara.</p>
        <x-attendance-session-summary :attendance="$application->attendance" />
        @if ($application->decision_reason)
            <p class="mt-4 text-sm font-medium text-slate-700">Catatan dari penyelenggara</p>
            <p class="mt-1 break-words whitespace-pre-line text-sm text-slate-600">{{ $application->decision_reason }}</p>
        @endif
    </x-section-card>
