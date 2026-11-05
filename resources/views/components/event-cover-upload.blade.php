@props(['event' => null])
<div x-data="eventCoverUpload(@js($event?->cover_image_path ? route('events.cover', $event) : null), @js((bool) old('remove_cover_image', false)))" class="space-y-3">
    <label for="cover_image" class="block text-sm font-semibold text-slate-800">Foto sampul kegiatan <span class="font-normal text-slate-500">(opsional)</span></label>
    <p id="cover_image_help" class="text-sm text-slate-500">JPG, PNG, atau WebP, maksimal 5 MB. Disarankan 1600 × 900 px (16:9).</p>
    <img x-show="preview" :src="preview || undefined" alt="Pratinjau foto sampul" class="aspect-video w-full max-w-md rounded-xl border border-slate-200 object-cover" x-cloak>
    <input id="cover_image" x-ref="file" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp" @change="choose($event)" aria-describedby="cover_image_help cover_image_error" class="block w-full rounded-xl border border-slate-200 p-3 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:font-semibold file:text-white">
    <input type="hidden" name="remove_cover_image" :value="removed ? '1' : '0'" value="{{ old('remove_cover_image', '0') }}">
    <button x-show="preview" type="button" @click="remove()" class="text-sm font-semibold text-red-700 hover:underline" x-cloak>Hapus foto</button>
    <p id="cover_image_error" class="text-sm text-red-600" role="alert"><span x-text="error"></span> @error('cover_image') {{ $message }} @enderror</p>
    <p class="text-xs text-slate-500">Tanpa foto, kartu memakai ilustrasi bawaan. Foto tidak otomatis menerbitkan kegiatan. Jika penyimpanan gagal, pilih ulang berkas foto.</p>
    @error('remove_cover_image')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
</div>
