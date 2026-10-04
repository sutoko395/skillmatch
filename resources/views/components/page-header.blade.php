@props(['title', 'description' => null])
<header class="mb-6"><h1 class="text-2xl font-semibold sm:text-3xl">{{ $title }}</h1>@if($description)<p class="mt-2 text-slate-600">{{ $description }}</p>@endif{{ $slot }}</header>
