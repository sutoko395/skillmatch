@props(['direction' => 'right'])
@php
    $path = match ($direction) {
        'up-right' => 'M7 17 17 7M7 7h10v10',
        'left' => 'M19 12H5m7 7-7-7 7-7',
        'down' => 'M12 5v14m-7-7 7 7 7-7',
        default => 'M5 12h14m-7-7 7 7-7 7',
    };
@endphp
<svg {{ $attributes->class(['ui-arrow-icon']) }} style="display:inline-block;width:16px;height:16px;flex-shrink:0;vertical-align:middle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" /></svg>
