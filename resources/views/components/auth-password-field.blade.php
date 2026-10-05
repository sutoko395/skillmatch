@props(['name' => 'password', 'label' => 'Kata sandi', 'autocomplete' => 'current-password'])
<div x-data="{ visible: false }">
    <x-input-label :for="$name" :value="$label" />
    <div class="relative mt-2">
        <x-text-input :id="$name" :name="$name" type="password" x-bind:type="visible ? 'text' : 'password'" :autocomplete="$autocomplete" required class="block w-full rounded-xl py-3 pr-28" :aria-invalid="$errors->has($name) ? 'true' : 'false'" :aria-describedby="$errors->has($name) ? $name.'-error' : null" />
        <button x-cloak type="button" @click="visible = !visible" :aria-pressed="visible.toString()" :aria-label="visible ? 'Sembunyikan {{ $label }}' : 'Tampilkan {{ $label }}'" class="absolute inset-y-0 right-1 rounded-lg px-3 text-xs font-semibold text-indigo-700 hover:bg-indigo-50" x-text="visible ? 'Sembunyikan' : 'Lihat'">Lihat</button>
    </div>
    <x-input-error :id="$name.'-error'" :messages="$errors->get($name)" class="mt-2" />
</div>
