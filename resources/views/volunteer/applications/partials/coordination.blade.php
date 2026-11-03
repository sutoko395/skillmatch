@if ($coordination !== null)
    <section id="koordinasi-peserta" aria-labelledby="coordination-title" class="order-first min-w-0 rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 id="coordination-title" class="text-lg font-bold text-slate-900">Koordinasi Peserta</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-600">Arahan peserta, titik kumpul, dan waktu kedatangan disampaikan melalui grup oleh organizer.</p>
        @if ($coordination['group_url'])
            <a href="{{ $coordination['group_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex min-h-[44px] items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Gabung grup peserta <span class="sr-only">(membuka tab baru)</span></a>
        @else
            <p class="mt-4 rounded-lg bg-slate-50 p-3 text-sm text-slate-600">Organizer belum membagikan tautan grup. Periksa kembali detail lamaran ini untuk pembaruan.</p>
        @endif
        @if ($coordination['pic_name'] || $coordination['pic_contact'])
            <dl class="mt-4 space-y-2 break-words text-sm">
                @if ($coordination['pic_name'])<div><dt class="font-semibold text-slate-800">PIC</dt><dd class="mt-1 text-slate-600">{{ $coordination['pic_name'] }}</dd></div>@endif
                @if ($coordination['pic_contact'])<div><dt class="font-semibold text-slate-800">Kontak PIC</dt><dd class="mt-1 text-slate-600">{{ $coordination['pic_contact'] }}</dd></div>@endif
            </dl>
        @endif
    </section>
@endif
