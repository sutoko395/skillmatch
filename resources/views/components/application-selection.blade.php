@props(['application'])
<x-section-card>
    <h2 class="mb-3 text-lg font-semibold text-slate-900">Keputusan seleksi</h2>
    @if($application->decision_reason)<p class="mb-3 whitespace-pre-line text-sm text-slate-700">Alasan: {{ $application->decision_reason }}</p>@endif
    @if(auth()->user()->role === 'organizer')
        <a href="{{ route('organizer.execution.show', $application->event) }}" class="font-semibold text-indigo-600">Pelaksanaan dan kehadiran</a>
        @if($application->status === 'under_review' && $application->event->lifecycle_status === 'upcoming' && now()->lt($application->event->starts_at) && (!$application->event->decision_deadline || now()->lt($application->event->decision_deadline)))
            <form method="POST" action="{{ route('organizer.applications.accept', $application) }}" class="mt-4" x-data="{busy:false}" @submit="if (busy) { $event.preventDefault(); } else { $nextTick(() => busy = true); }" @pageshow.window="busy=false">
                @csrf
                <x-primary-button type="submit" x-bind:disabled="busy"><span x-show="!busy">Terima pelamar</span><span x-show="busy" x-cloak>Menyimpan penerimaan...</span></x-primary-button>
            </form>
            <form method="POST" action="{{ route('organizer.applications.reject', $application) }}" class="mt-4 space-y-3">
                @csrf <label class="block">Alasan penolakan<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300">{{ old('reason') }}</textarea></label>
                <x-primary-button>Tolak pelamar</x-primary-button>
            </form>
        @elseif($application->status === 'under_review')
            <p class="mt-3 text-sm text-slate-600">Batas seleksi berakhir atau kegiatan tidak lagi tersedia untuk keputusan. Lamaran tertunda dapat ditutup dengan alasan melalui halaman Pelaksanaan.</p>
        @elseif($application->status === 'accepted' && $application->event->lifecycle_status === 'upcoming' && now()->lt($application->event->starts_at))
            <form method="POST" action="{{ route('organizer.applications.withdraw', $application) }}" class="mt-4 space-y-3">@csrf
                <label class="block">Alasan pengunduran peserta<textarea name="reason" required minlength="5" maxlength="1000" class="block w-full rounded-lg border-slate-300"></textarea></label>
                <x-primary-button>Catat pengunduran peserta</x-primary-button>
            </form>
        @endif
    @elseif(in_array($application->status, ['submitted', 'assessment_pending', 'under_review']))
        <form method="POST" action="{{ route('volunteer.applications.withdraw', $application) }}" class="space-y-3">@csrf
            <p class="text-sm text-slate-600">Penarikan bersifat final. Kamu tidak dapat melamar ulang pada event ini.</p>
            <label class="block">Alasan penarikan (opsional)<textarea name="reason" maxlength="1000" class="block w-full rounded-lg border-slate-300"></textarea></label>
            <x-primary-button>Tarik lamaran</x-primary-button>
        </form>
    @endif
</x-section-card>
