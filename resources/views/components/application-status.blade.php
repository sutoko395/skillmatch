@props(['application'])
@php
    $label = match ($application->status) {
        'draft' => 'Draf',
        'submitted' => $application->screeningResult?->passed ? 'Lolos penyaringan — menunggu pencocokan' : 'Menunggu evaluasi',
        'screening_failed' => 'Gagal penyaringan',
        'assessment_pending' => 'Menunggu asesmen',
        'under_review' => 'Siap ditinjau',
        'accepted' => 'Diterima',
        'rejected' => 'Ditolak',
        'withdrawn' => 'Ditarik',
        'cancelled' => 'Dibatalkan',
        default => ucfirst(str_replace('_', ' ', $application->status)),
    };
    $color = match ($application->status) {
        'screening_failed', 'rejected' => 'error',
        'accepted' => 'success',
        'draft', 'submitted', 'assessment_pending' => 'pending',
        default => 'default',
    };
@endphp
<x-status-badge :status="$color">{{ $label }}</x-status-badge>
