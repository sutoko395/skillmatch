    {{-- The panel layout already renders success and validation messages. --}}
    @if ($canOpenAssessment)
        <div id="assessment-summary" class="scroll-mt-24">
            <x-volunteer-application-assessment :application="$application" :assessment="$assessmentSummary" :deadline="$recruitmentDates['assessment_deadline']" featured />
        </div>
    @else
        <section aria-label="Status lamaran" class="rounded-2xl border p-5 sm:p-6 {{ $bannerColor }}">
            <div class="flex items-start gap-4">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white/70" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                        @if ($status === 'accepted' && !$eventCancelled)
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6" />
                        @else
                            <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 11v5m0-9v.01" />
                        @endif
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <h2 class="text-xl font-bold">{{ $bannerTitle }}</h2>
                    <p class="mt-1 text-sm leading-relaxed">{{ $bannerText }}</p>
                    @if ($status !== 'accepted')
                        <div class="mt-3"><x-application-status :application="$application" /></div>
                    @endif
                    @if ($status === 'draft')
                        <form method="POST" action="{{ route('volunteer.applications.submit', $application) }}" class="mt-4">
                            @csrf
                            <x-button type="submit">Kirimkan Lamaran</x-button>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    @endif

