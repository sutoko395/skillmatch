@props(['status' => 'pending'])
@php($colors = match($status) { 'active', 'success' => 'bg-emerald-50 text-emerald-800', 'inactive', 'error' => 'bg-rose-50 text-rose-800', 'pending' => 'bg-amber-50 text-amber-800', default => 'bg-slate-100 text-slate-700' })
<span {{ $attributes->class(['inline-flex rounded-full px-3 py-1 text-sm font-medium', $colors]) }}>{{ $slot }}</span>
