@props(['position', 'forVolunteer' => false])
@if($position->suggested_min_age !== null || $position->suggested_max_age !== null)
    <p {{ $attributes->class(['text-xs leading-relaxed text-slate-500']) }}>
        <span class="font-medium text-slate-700">{{ $forVolunteer ? 'Usia pendaftar:' : 'Usia yang disarankan:' }}</span>
        @if($position->suggested_min_age !== null && $position->suggested_max_age !== null)
            {{ $position->suggested_min_age }}&ndash;{{ $position->suggested_max_age }} tahun.
        @elseif($position->suggested_min_age !== null)
            {{ $position->suggested_min_age }} tahun ke atas.
        @else
            Hingga {{ $position->suggested_max_age }} tahun.
        @endif
        @unless($forVolunteer)Usia pada tanggal mulai kegiatan. Hanya informasi, bukan syarat melamar.@endunless
    </p>
@endif
