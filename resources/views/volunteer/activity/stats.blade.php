<div class="va-stats grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">
            Skill Saya
        </p>

        <p class="mt-2 text-3xl font-bold text-indigo-600">
            {{ $skills->count() }}
        </p>

        <p class="mt-1 text-sm text-slate-500">
            {{ count($eligibility['skills'] ?? []) }} skill aktif pada profil.
        </p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">
            Status Profil
        </p>

        @if($eligibility['complete'] ?? false)
            <div class="va-status-pill mt-3 inline-flex items-center rounded-full bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700">
                Profil Lengkap
            </div>
        @else
            <div class="va-status-pill mt-3 inline-flex items-center rounded-full bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700">
                Perlu Dilengkapi
            </div>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500">
                Lamaran Terkirim
            </p>

            <span class="rounded-xl bg-indigo-50 p-2 text-indigo-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z"/>
                </svg>
            </span>
        </div>

        <p class="mt-2 text-3xl font-bold text-indigo-600">
            {{ $applicationCount }}
        </p>

        <p class="mt-1 text-sm text-slate-500">
            Total lamaran yang sudah dikirim.
        </p>
    </div>

</div>
