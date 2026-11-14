@props(['status', 'context' => null])
@php
    $labels = [
        'draft' => 'Draf', 'pending' => 'Menunggu moderasi', 'approved' => 'Disetujui',
        'rejected' => 'Ditolak', 'unpublished' => 'Belum dipublikasikan',
        'published' => 'Dipublikasikan', 'suspended' => 'Publikasi ditangguhkan',
        'upcoming' => 'Belum dimulai', 'ongoing' => 'Berlangsung',
        'completed' => 'Selesai', 'cancelled' => 'Dibatalkan',
        'paid' => 'Lunas', 'expired' => 'Kedaluwarsa', 'failed' => 'Gagal',
    ];
    $tone = match ($status) {
        'approved', 'completed', 'paid' => 'success',
        'published', 'ongoing' => 'blue',
        'pending' => 'warning',
        'rejected', 'failed', 'cancelled', 'suspended' => 'danger',
        default => 'neutral',
    };
    $label = $context === 'Pembayaran' && $status === 'pending' ? 'Menunggu pembayaran' : ($labels[$status] ?? 'Status belum dikenali');
@endphp
<span {{ $attributes->class(['organizer-status', 'organizer-status--'.$tone]) }}>
    @if($context)<span class="organizer-status-context">{{ $context }}:</span>@endif
    {{ $label }}
</span>
