@foreach(['success', 'error', 'status'] as $kind)
    @if(session($kind))<p role="status" class="mb-4 rounded-xl border border-slate-200 bg-white p-4">{{ session($kind) }}</p>@endif
@endforeach
@if($errors->any())
    <div role="alert" class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800"><p class="font-semibold">Periksa kembali data yang diisi.</p><ul class="mt-2 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
