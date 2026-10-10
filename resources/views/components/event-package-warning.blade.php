<div aria-live="polite">
    <p class="text-sm text-slate-600" x-show="selectedPackage" x-cloak>
        Batas pendaftaran paket ini: <span x-text="selectedPackage?.max_registration_days"></span> hari sejak pendaftaran dibuka.
    </p>
    <p role="alert" x-show="exceedsLimit" x-cloak x-text="warning"
        class="mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900"></p>
</div>
