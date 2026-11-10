    <header class="space-y-4">
        <a href="{{ route('volunteer.applications.index') }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7" /></svg>
            Lamaran Saya
        </a>
        <x-page-header title="Detail Lamaran" :description="$application->event->title . ' · Posisi: ' . $application->position->name" />
    </header>
