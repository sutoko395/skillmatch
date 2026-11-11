@if(now() < $event->registration_opens_at)<p>Pendaftaran belum dibuka.</p>
@elseif(now() >= $event->registration_deadline)<p>Pendaftaran sudah ditutup.</p>
@elseif($event->entitlement->submitted_applications >= $event->entitlement->max_applications)<p>Batas lamaran kegiatan sudah tercapai.</p>
@elseif(!Route::has('volunteer.applications.store'))<p class="text-slate-600">Pengajuan lamaran belum tersedia.</p>
@else
@guest<a class="text-indigo-700 underline" href="{{ route('events.join',$event) }}">Masuk untuk melamar</a>@else
@if(auth()->user()->role==='volunteer')
@if(!auth()->user()->hasVerifiedEmail())
<p class="mb-3 text-sm text-slate-600">Verifikasi email Anda sebelum mulai melamar.</p>
<a class="text-indigo-700 underline" href="{{ route('events.join', $event) }}">Verifikasi email untuk melamar</a>
@else
<form method="POST" action="{{ route('volunteer.applications.store',$position) }}">@csrf<x-button>Pilih Posisi & Mulai Lamaran</x-button></form>
@endif
@else<p>Lamaran hanya untuk akun Volunteer.</p>@endif
@endguest
@endif
