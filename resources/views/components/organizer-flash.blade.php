<div class="organizer-toast-stack" aria-label="Pemberitahuan">
    @foreach(['success', 'error', 'status'] as $kind)
        @if(session($kind))
            <div class="organizer-toast organizer-toast--{{ $kind }}"
                role="{{ $kind === 'error' ? 'alert' : 'status' }}"
                x-data="{
                    visible: true,
                    timer: null,
                    start() {
                        clearTimeout(this.timer);
                        @if($kind !== 'error')this.timer = setTimeout(() => this.visible = false, 5000);@endif
                    },
                    pause() { clearTimeout(this.timer); },
                    destroy() { clearTimeout(this.timer); }
                }"
                x-init="start()" x-show="visible"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                @mouseenter="pause()" @mouseleave="start()"
                @focusin="pause()" @focusout="start()">
                <span class="organizer-toast-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        @if($kind === 'success')
                            <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6" />
                        @elseif($kind === 'error')
                            <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 7v6m0 4h.01" />
                        @else
                            <circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M12 11v6m0-10h.01" />
                        @endif
                    </svg>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="organizer-toast-title">{{ match($kind) { 'success' => 'Berhasil', 'error' => 'Tidak berhasil', default => 'Informasi' } }}</p>
                    <p class="organizer-toast-message">{{ session($kind) }}</p>
                </div>
                <button type="button" class="organizer-toast-close" aria-label="Tutup pemberitahuan" @click="visible = false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="m6 6 12 12M6 18 18 6" /></svg>
                </button>
            </div>
        @endif
    @endforeach
</div>
@if($errors->any())
    <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800"><p class="font-semibold">Periksa kembali data yang diisi.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
