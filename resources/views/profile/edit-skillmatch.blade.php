<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lengkapi Profil SkillMatch') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(Auth::user()->role === 'volunteer')
                <!-- Form Profil Volunteer -->
                <div class="p-6 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profil & Skill Volunteer</h3>
                    <form action="{{ route('profile.volunteer.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nomor Telepon/WA</label>
                            <input type="text" name="phone" value="{{ old('phone', $volunteerProfile->phone ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lokasi Dominan (Kota)</label>
                            <input type="text" name="location" value="{{ old('location', $volunteerProfile->location ?? '') }}" placeholder="Contoh: Malang" class="mt-1 w-full border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ketersediaan Waktu</label>
                            <select name="availability" class="mt-1 w-full border-gray-300 rounded-md" required>
                                <option value="Weekend" {{ ($volunteerProfile->availability ?? '') == 'Weekend' ? 'selected' : '' }}>Weekend Only</option>
                                <option value="Weekday" {{ ($volunteerProfile->availability ?? '') == 'Weekday' ? 'selected' : '' }}>Weekday Only</option>
                                <option value="Flexibel" {{ ($volunteerProfile->availability ?? '') == 'Flexibel' ? 'selected' : '' }}>Fleksibel (Kapan Saja)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Bio Singkat</label>
                            <textarea name="bio" class="mt-1 w-full border-gray-300 rounded-md" rows="3">{{ old('bio', $volunteerProfile->bio ?? '') }}</textarea>
                        </div>

                        <hr class="my-6">
                        <h4 class="font-semibold text-gray-800">Pilih Skill & Level Kemampuan</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($skills as $skill)
                                <div class="flex items-center justify-between p-3 border rounded-md">
                                    <span class="font-medium text-sm text-gray-700">{{ $skill->name }}</span>
                                    <select name="skills[{{ $skill->id }}]" class="text-sm border-gray-300 rounded-md">
                                        <option value="">-- Tidak Memiliki --</option>
                                        <option value="beginner" {{ ($userSkills[$skill->id] ?? '') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                        <option value="intermediate" {{ ($userSkills[$skill->id] ?? '') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                        <option value="advanced" {{ ($userSkills[$skill->id] ?? '') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                        <option value="expert" {{ ($userSkills[$skill->id] ?? '') == 'expert' ? 'selected' : '' }}>Expert</option>
                                    </select>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 mt-4">Simpan Profil Volunteer</button>
                    </form>
                </div>
            @elseif(Auth::user()->role === 'organizer')
                <!-- Form Profil Organizer -->
                <div class="p-6 bg-white shadow rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Profil Organisasi / Komunitas</h3>
                    <form action="{{ route('profile.organizer.update') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Organisasi / Komunitas</label>
                            <input type="text" name="organization_name" value="{{ old('organization_name', $organizerProfile->organization_name ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Penanggung Jawab (Contact Person)</label>
                            <input type="text" name="contact_person" value="{{ old('contact_person', $organizerProfile->contact_person ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nomor Telepon / WA</label>
                            <input type="text" name="phone" value="{{ old('phone', $organizerProfile->phone ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alamat Kantor / Sekretariat</label>
                            <input type="text" name="address" value="{{ old('address', $organizerProfile->address ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Website / Media Sosial</label>
                            <input type="text" name="website" value="{{ old('website', $organizerProfile->website ?? '') }}" class="mt-1 w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi Organisasi</label>
                            <textarea name="description" class="mt-1 w-full border-gray-300 rounded-md" rows="3">{{ old('description', $organizerProfile->description ?? '') }}</textarea>
                        </div>

                        <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">Simpan Profil Organizer</button>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>