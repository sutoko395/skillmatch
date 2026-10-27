@props(['event' => null])
<div class="grid gap-5 md:grid-cols-2">
    <x-form-field
        name="assessment_deadline"
        label="Tenggat assessment (WIB)"
        type="datetime-local"
        :value="$event?->assessment_deadline?->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')"
        helper="Setelah pendaftaran ditutup dan sebelum batas keputusan EO."
        required
    />
    <x-form-field
        name="decision_deadline"
        label="Batas akhir keputusan EO (WIB)"
        type="datetime-local"
        :value="$event?->decision_deadline?->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i')"
        helper="Sebelum kegiatan dimulai. Keputusan langsung terlihat ketika ditetapkan."
        required
    />
</div>
