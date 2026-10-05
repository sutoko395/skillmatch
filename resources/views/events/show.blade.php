@extends('layouts.public')
@section('title',$event->title)
@section('content')
<x-page-header :title="$event->title" :description="$event->organizer->organizerProfile?->organization_name ?? $event->organizer->name" />
<div class="space-y-6"><x-section-card><p class="whitespace-pre-line">{{ $event->description }}</p><p class="mt-4">{{ $event->location }} &middot; {{ $event->cityRecord?->name }}</p><p>{{ $event->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $event->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p><p class="mt-2">Pendaftaran {{ $event->registration_opens_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} sampai {{ $event->registration_deadline->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</p></x-section-card>
@foreach($event->positions as $position)<x-section-card><h2 class="text-xl font-semibold">{{ $position->name }}</h2><p class="mt-2">Kuota: {{ $position->quota }}</p><p class="mt-2 whitespace-pre-line">{{ $position->description }}</p>
<h3 class="mt-4 font-semibold">Skill</h3><ul class="mt-2 list-inside list-disc">@foreach($position->positionSkills as $s)<li>{{ $s->skill->name }} - {{ $s->minimum_level }} ({{ $s->is_required ? 'wajib' : 'preferensi' }})</li>@endforeach</ul>
<h3 class="mt-4 font-semibold">Jadwal tugas (WIB)</h3>@foreach($position->schedules as $slot)<p>{{ $slot->starts_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} - {{ $slot->ends_at->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }}</p>@endforeach
@if($position->required_full_availability)<p class="mt-2">Wajib tersedia sepanjang jadwal.</p>@endif
@if($position->required_same_city)<p class="mt-2">Wajib berdomisili di kota event.</p>@endif
@foreach($position->requirements as $r)<p class="mt-2">{{ $r->name }} ({{ $r->is_required ? 'wajib' : 'preferensi' }}): {{ $r->description }} @if($r->kind==='document') &middot; Dokumen {{ $r->document_type }} @endif</p>@endforeach
<div class="mt-5">
@if(now() < $event->registration_opens_at)<p>Pendaftaran belum dibuka.</p>
@elseif(now() >= $event->registration_deadline)<p>Pendaftaran sudah ditutup.</p>
@elseif($event->entitlement->submitted_applications >= $event->entitlement->max_applications)<p>Batas lamaran kegiatan sudah tercapai.</p>
@elseif(!Route::has('volunteer.applications.store'))<p class="text-slate-600">Pengajuan lamaran belum tersedia.</p>
@else
@guest<a class="text-indigo-700 underline" href="{{ route('events.join',$event) }}">Masuk untuk melamar</a>@else
@if(auth()->user()->role==='volunteer')<form method="POST" action="{{ route('volunteer.applications.store',$position) }}">@csrf<x-button>Pilih posisi dan mulai lamaran</x-button></form>@else<p>Lamaran hanya untuk akun Volunteer.</p>@endif
@endguest
@endif</div></x-section-card>@endforeach</div>
@endsection
