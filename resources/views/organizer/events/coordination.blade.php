<x-section-card id="koordinasi-peserta">
    <h2 class="text-lg font-bold text-slate-900">Koordinasi Peserta</h2>
    <p class="mt-2 text-sm text-slate-600">Opsional. Tautan grup dan kontak PIC hanya terlihat oleh Anda dan volunteer yang sudah diterima. Sampaikan arahan peserta, titik kumpul, serta waktu kedatangan melalui grup.</p>
    @if (!in_array($event->lifecycle_status, ['completed', 'cancelled'], true))
        <form method="POST" action="{{ route('organizer.events.coordination.update', $event) }}" class="mt-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="revision" value="{{ old('revision', $coordination['revision']) }}">
            <x-input-error :messages="$errors->get('revision')" />
            <x-form-field name="group_url" label="Tautan grup" type="url" :value="$coordination['group_url']" maxlength="2048" placeholder="https://…" helper="Gunakan tautan undangan HTTPS. Kosongkan untuk menarik tautan dari aplikasi." />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form-field name="pic_name" label="Nama PIC" :value="$coordination['pic_name']" maxlength="100" />
                <x-form-field name="pic_contact" label="Kontak PIC" :value="$coordination['pic_contact']" maxlength="255" helper="Nomor telepon, email, atau nama pengguna yang dapat dihubungi." />
            </div>
            <p class="text-sm text-slate-500">Perubahan akan diberitahukan kepada peserta yang sudah diterima. Keanggotaan grup dikelola melalui platform grup.</p>
            <x-button>Simpan koordinasi</x-button>
        </form>
    @else
        <dl class="mt-4 space-y-3 break-words text-sm">
            <div><dt class="font-semibold">Tautan grup</dt><dd>{{ $coordination['group_url'] ?: 'Belum diisi' }}</dd></div>
            <div><dt class="font-semibold">Nama PIC</dt><dd>{{ $coordination['pic_name'] ?: 'Belum diisi' }}</dd></div>
            <div><dt class="font-semibold">Kontak PIC</dt><dd>{{ $coordination['pic_contact'] ?: 'Belum diisi' }}</dd></div>
        </dl>
        <p class="mt-3 text-sm text-slate-500">Koordinasi tersimpan sebagai riwayat dan tidak dapat diubah setelah event selesai atau dibatalkan.</p>
    @endif
</x-section-card>
