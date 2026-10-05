@props(['title', 'description'])
<x-section-card><h2 class="text-lg font-semibold">{{ $title }}</h2><p class="mt-2 text-slate-600">{{ $description }}</p><div class="mt-4">{{ $slot }}</div></x-section-card>
