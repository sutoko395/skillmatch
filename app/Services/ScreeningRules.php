<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ScreeningRules
{
    private const LEVELS = ['beginner' => 0, 'intermediate' => 1, 'advanced' => 2, 'expert' => 3];

    public function evaluate(array $snapshot): array
    {
        Validator::make($snapshot, [
            'rule_version' => 'required|in:match-v1.4',
            'profile_eligibility' => 'required|array',
            'profile_eligibility.complete' => 'required|boolean',
            'profile_eligibility.missing_fields' => 'present|array',
            'profile_eligibility.missing_fields.*' => 'required|string',
            'profile.city_id' => 'present|nullable|integer|min:1',
            'city_id' => 'required|integer|min:1',
            'profile.skills' => 'present|array',
            'profile.skills.*.id' => 'required|integer|min:1|distinct',
            'profile.skills.*.level' => 'required|in:beginner,intermediate,advanced,expert',
            'profile.skills.*.name' => 'nullable|string',
            'skills' => 'required|array|min:1',
            'skills.*.id' => 'required|integer|min:1|distinct',
            'skills.*.minimum_level' => 'required|in:beginner,intermediate,advanced,expert',
            'skills.*.is_required' => 'required|boolean',
            'schedules' => 'required|array|min:1',
            'schedules.*.start' => 'required|date',
            'schedules.*.end' => 'required|date',
            'profile.availability_slots' => 'present|array',
            'profile.availability_slots.*.starts_at' => 'required|date',
            'profile.availability_slots.*.ends_at' => 'required|date',
            'required_full_availability' => 'required|boolean',
            'required_same_city' => 'required|boolean',
            'requirements' => 'present|array',
            'requirements.*.name' => 'required|string',
            'requirements.*.kind' => 'required|in:document,manual',
            'requirements.*.document_type' => 'nullable|in:cv,supporting|required_if:requirements.*.kind,document',
            'requirements.*.is_required' => 'required|boolean',
            'documents' => 'present|array',
            'documents.*.id' => 'required|integer|min:1|distinct',
            'documents.*.document_type' => 'required|in:cv,supporting',
            'documents.*.ready_at' => 'required|date',
        ])->validate();

        $eligibility = $snapshot['profile_eligibility'];
        if ((bool) $eligibility['complete'] !== ($eligibility['missing_fields'] === [])) {
            throw ValidationException::withMessages(['snapshot' => 'Bukti kelengkapan profil tidak konsisten.']);
        }
        $fields = ['role' => 'role Volunteer', 'name' => 'nama', 'email' => 'email', 'phone' => 'telepon',
            'birth_date' => 'tanggal lahir', 'gender' => 'gender', 'address' => 'alamat', 'bio' => 'bio',
            'city_id' => 'kota aktif', 'skills' => 'skill aktif dengan level valid', 'availability_slots' => 'ketersediaan jadwal'];
        $checks = [$this->check('profile', 'Profil lengkap', true, (bool) $eligibility['complete'],
            $eligibility['complete'] ? 'Data profil lengkap.' : 'Profil belum lengkap: '.implode(', ', array_map(fn ($field) => $fields[$field] ?? 'data profil', $eligibility['missing_fields'])).'.')];

        $profileSkills = collect($snapshot['profile']['skills'])->keyBy('id');
        foreach ($snapshot['skills'] as $skill) {
            $candidate = $profileSkills->get($skill['id']);
            $required = (bool) $skill['is_required'];
            $matches = $candidate && self::LEVELS[$candidate['level']] >= self::LEVELS[$skill['minimum_level']];
            $name = $candidate['name'] ?? 'Skill #'.$skill['id'];
            $checks[] = $this->check('skill:'.$skill['id'], $name, $required, $matches,
                ! $required ? 'Skill preferensi; tidak memengaruhi kelolosan.' : ($matches
                    ? 'Level skill memenuhi minimum '.$skill['minimum_level'].'.'
                    : 'Minimum '.$skill['minimum_level'].'; level pelamar '.($candidate['level'] ?? 'belum tercatat').'.'));
        }

        foreach ($snapshot['requirements'] as $index => $requirement) {
            $manual = $requirement['kind'] === 'manual';
            $required = ! $manual && (bool) $requirement['is_required'];
            $ready = ! $manual && collect($snapshot['documents'])->contains('document_type', $requirement['document_type']);
            $checks[] = $this->check('requirement:'.$index, $requirement['name'], $required, $ready,
                $manual ? 'Ditinjau manual oleh Organizer; tidak dinilai otomatis.' : (! $required
                    ? 'Dokumen tidak diwajibkan.' : ($ready ? 'Dokumen wajib tersimpan saat submit.' : 'Dokumen wajib belum dilampirkan saat submit.')), $manual);
        }

        $schedules = $this->intervals($snapshot['schedules'], 'start', 'end');
        $available = $this->intervals($snapshot['profile']['availability_slots'], 'starts_at', 'ends_at');
        $covered = collect($schedules)->every(fn ($schedule) => collect($available)->contains(
            fn ($slot) => $slot[0] <= $schedule[0] && $slot[1] >= $schedule[1]));
        $required = (bool) $snapshot['required_full_availability'];
        $checks[] = $this->check('availability', 'Ketersediaan jadwal', $required, $covered,
            ! $required ? 'Ketersediaan penuh tidak diwajibkan.' : ($covered ? 'Mencakup seluruh jadwal posisi.' : 'Ketersediaan belum mencakup seluruh jadwal posisi.'));
        $required = (bool) $snapshot['required_same_city'];
        $sameCity = (int) $snapshot['city_id'] === (int) $snapshot['profile']['city_id'];
        $checks[] = $this->check('city', 'Domisili', $required, $sameCity,
            ! $required ? 'Domisili di kota event tidak diwajibkan.' : ($sameCity ? 'Kota pelamar sama dengan kota event.' : 'Kota pelamar berbeda dari kota event.'));

        return ['passed' => ! collect($checks)->contains(fn ($check) => $check['required'] && ! $check['passed']),
            'checks' => $checks, 'rule_version' => $snapshot['rule_version']];
    }

    private function check(string $code, string $label, bool $required, bool $matches, string $reason, bool $manual = false): array
    {
        return ['code' => $code, 'label' => $label, 'required' => $required, 'passed' => ! $required || $matches, 'reason' => $reason, 'manual' => $manual];
    }

    private function intervals(array $rows, string $startKey, string $endKey): array
    {
        $intervals = [];
        foreach ($rows as $row) {
            // Snapshot timestamps must carry an offset; never guess a historical timezone.
            foreach ([$startKey, $endKey] as $key) {
                if (! preg_match('/(?:Z|[+-]\d{2}:\d{2})$/', $row[$key])) {
                    throw ValidationException::withMessages(['snapshot' => 'Timezone jadwal snapshot tidak tersedia.']);
                }
            }
            $start = CarbonImmutable::parse($row[$startKey])->getTimestamp();
            $end = CarbonImmutable::parse($row[$endKey])->getTimestamp();
            if ($end <= $start) {
                throw ValidationException::withMessages(['snapshot' => 'Interval jadwal snapshot tidak valid.']);
            }
            $intervals[] = [$start, $end];
        }
        usort($intervals, fn ($a, $b) => $a[0] <=> $b[0]);
        $merged = [];
        foreach ($intervals as [$start, $end]) {
            $last = count($merged) - 1;
            if ($last >= 0 && $start <= $merged[$last][1]) {
                $merged[$last][1] = max($merged[$last][1], $end);
            } else {
                $merged[] = [$start, $end];
            }
        }

        return $merged;
    }
}
