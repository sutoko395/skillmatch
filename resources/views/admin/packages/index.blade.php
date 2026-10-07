@extends('admin.layouts.sidebar')

@section('title', 'Paket')

@section('content')

<div
    x-data="{
        createPackage: false,
        editPackage: null
    }"
    class="w-full max-w-7xl mx-auto space-y-6"
>

    @include('components.flash')

    <x-page-header
        title="Paket"
        description="Kelola harga dan manfaat paket. Pembelian lama tetap memakai snapshot semula."
    />

    <div class="flex justify-end">
        <button
            type="button"
            @click="createPackage = true"
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
                class="h-5 w-5"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 5v14m-7-7h14"
                />
            </svg>

            Tambah Paket
        </button>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">

        @forelse($packages as $package)

            <x-package-card
                :package="$package"
                :show-status="true"
            >
                <button
                    type="button"
                    @click="editPackage = {{ $package->id }}"
                    class="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        class="h-4 w-4"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M16.862 4.487a2.25 2.25 0 113.182 3.182L8.25 19.463 4.5 20.25l.788-3.75L16.862 4.487z"
                        />
                    </svg>

                    Edit Paket
                </button>
            </x-package-card>

        @empty

            <div class="md:col-span-2 xl:col-span-3">
                <x-empty-state
                    title="Belum ada paket"
                    description="Tambahkan harga dan manfaat yang telah disepakati."
                />
            </div>

        @endforelse

    </div>

    @if($packages->hasPages())
        <div class="pt-2">
            {{ $packages->links() }}
        </div>
    @endif

    <template x-teleport="body">

        <div
            x-show="createPackage"
            x-cloak
            class="fixed inset-0 z-[9999] flex min-h-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="createPackage = false"
            ></div>

            <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">

                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

                    <div>
                        <h2 class="text-xl font-bold text-slate-800">
                            Tambah Paket
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Tambahkan harga dan manfaat paket baru.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="createPackage = false"
                        class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="2"
                            stroke="currentColor"
                            class="h-5 w-5"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M6 18 18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                </div>

                <form
                    method="POST"
                    action="{{ route('admin.packages.store') }}"
                    class="max-h-[80vh] overflow-y-auto"
                >

                    @csrf

                    <div class="space-y-5 p-6">

                        <x-form-field
                            name="name"
                            label="Nama Paket"
                            :value="old('name')"
                            required
                        />

                        <div class="grid gap-4 md:grid-cols-2">

                            <x-form-field
                                name="price"
                                label="Harga (Rupiah)"
                                type="number"
                                :value="old('price')"
                                min="0"
                                required
                            />

                            <x-form-field
                                name="max_positions"
                                label="Maksimal Posisi"
                                type="number"
                                :value="old('max_positions')"
                                min="1"
                                required
                            />

                            <x-form-field
                                name="max_applications"
                                label="Maksimal Lamaran Terkirim"
                                type="number"
                                :value="old('max_applications')"
                                min="1"
                                required
                            />

                            <x-form-field
                                name="max_registration_days"
                                label="Maksimal Hari Pendaftaran"
                                type="number"
                                :value="old('max_registration_days')"
                                min="1"
                                required
                            />

                        </div>

                        <x-form-field
                            name="is_active"
                            label="Status"
                        >
                            <select
                                id="is_active_create"
                                name="is_active"
                                class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="1" @selected(old('is_active', '1') === '1')>
                                    Aktif
                                </option>

                                <option value="0" @selected(old('is_active') === '0')>
                                    Nonaktif
                                </option>
                            </select>
                        </x-form-field>

                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">

                        <button
                            type="button"
                            @click="createPackage = false"
                            class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                        >
                            Simpan Paket
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </template>

    <template x-teleport="body">

        <div
            x-show="editPackage"
            x-cloak
            class="fixed inset-0 z-[9999] flex min-h-screen items-center justify-center p-4"
        >

            <div
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="editPackage = null"
            ></div>

            @foreach($packages as $package)

                <div
                    x-show="editPackage === {{ $package->id }}"
                    x-cloak
                    class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl"
                >

                    <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

                        <div>
                            <h2 class="text-xl font-bold text-slate-800">
                                Edit Paket
                            </h2>

                            <p class="mt-1 text-sm text-slate-500">
                                Perbarui konfigurasi paket {{ $package->name }}.
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="editPackage = null"
                            class="flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 18 18 6M6 6l12 12"
                                />
                            </svg>
                        </button>

                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.packages.update', $package) }}"
                        class="max-h-[80vh] overflow-y-auto"
                    >

                        @csrf
                        @method('PUT')

                        <div class="space-y-5 p-6">

                            <x-form-field
                                name="name"
                                label="Nama Paket"
                                :value="$package->name"
                                required
                            />

                            <div class="grid gap-4 md:grid-cols-2">

                                <x-form-field
                                    name="price"
                                    label="Harga (Rupiah)"
                                    type="number"
                                    :value="$package->price"
                                    min="0"
                                    required
                                />

                                <x-form-field
                                    name="max_positions"
                                    label="Maksimal Posisi"
                                    type="number"
                                    :value="$package->max_positions"
                                    min="1"
                                    required
                                />

                                <x-form-field
                                    name="max_applications"
                                    label="Maksimal Lamaran Terkirim"
                                    type="number"
                                    :value="$package->max_applications"
                                    min="1"
                                    required
                                />

                                <x-form-field
                                    name="max_registration_days"
                                    label="Maksimal Hari Pendaftaran"
                                    type="number"
                                    :value="$package->max_registration_days"
                                    min="1"
                                    required
                                />

                            </div>

                            <x-form-field
                                name="is_active"
                                label="Status"
                            >
                                <select
                                    id="is_active_{{ $package->id }}"
                                    name="is_active"
                                    class="mt-2 w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option
                                        value="1"
                                        @selected($package->is_active)
                                    >
                                        Aktif
                                    </option>

                                    <option
                                        value="0"
                                        @selected(! $package->is_active)
                                    >
                                        Nonaktif
                                    </option>
                                </select>
                            </x-form-field>

                        </div>

                        <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">

                            <button
                                type="button"
                                @click="editPackage = null"
                                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                            >
                                Batal
                            </button>

                            <button
                                type="submit"
                                class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                            >
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>

            @endforeach

        </div>

    </template>

</div>

@endsection