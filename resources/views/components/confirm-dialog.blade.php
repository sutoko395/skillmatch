@props(['name', 'title'])
<x-modal :name="$name" focusable><div class="p-6"><h2 class="text-lg font-semibold">{{ $title }}</h2><div class="mt-4">{{ $slot }}</div><x-secondary-button class="mt-4" x-on:click="$dispatch('close')">Batal</x-secondary-button></div></x-modal>
