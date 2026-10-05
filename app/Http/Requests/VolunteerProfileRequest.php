<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VolunteerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'volunteer' && $this->user()->can('updateProfile', $this->user());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'gender' => ['required', Rule::in(['male', 'female'])],
            'address' => ['required', 'string', 'max:1000'],
            'bio' => ['required', 'string', 'max:700', function ($attribute, $value, $fail) {
                if (count(preg_split('/\s+/u', trim(strip_tags($value)), -1, PREG_SPLIT_NO_EMPTY)) > 100) {
                    $fail('Bio maksimal 100 kata.');
                }
            }],
            'skills' => ['required', 'array', 'min:1', 'max:100'],
            'skills.*' => ['array:skill_id,level'],
            'skills.*.skill_id' => ['required', 'integer', 'distinct', Rule::exists('skills', 'id')->where('is_active', true)],
            'skills.*.level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced', 'expert'])],
            'availability_slots' => ['required', 'array', 'min:1', 'max:100'],
            'availability_slots.*' => ['array:starts_at,ends_at'],
            'availability_slots.*.starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'availability_slots.*.ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:availability_slots.*.starts_at'],
            'role' => ['prohibited'], 'user_id' => ['prohibited'], 'is_active' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'skills.*.skill_id.distinct' => 'Skill yang sama tidak boleh dipilih dua kali.',
            'city_id.exists' => 'Pilih kota aktif dari daftar.',
            'skills.*.skill_id.exists' => 'Pilih skill aktif dari daftar.',
            'availability_slots.*.ends_at.after' => 'Waktu akhir harus sesudah waktu mulai (WIB).',
        ];
    }
}
