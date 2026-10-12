@props(['application'])
@php
    $label = match ($application->status) {
        'draft' => 'Draft',
        'submitted' => $application->screeningResult?->passed ? 'Lolos screening — menunggu pencocokan' : 'Menunggu evaluasi',
        'screening_failed' => 'Gagal screening',
        'assessment_pending' => 'Menunggu assessment',
        'under_review' => 'Siap Ditinjau',
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
