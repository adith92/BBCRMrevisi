@props(['status', 'link' => null])

@php
$colors = [
    'pending'     => 'bg-yellow-100 text-yellow-800',
    'confirmed'   => 'bg-blue-100 text-blue-800',
    'on_trip'     => 'bg-purple-100 text-purple-800',
    'completed'   => 'bg-green-100 text-green-800',
    'cancelled'   => 'bg-red-100 text-red-800',
    'paid'        => 'bg-green-100 text-green-800',
    'sent'        => 'bg-blue-100 text-blue-800',
    'draft'       => 'bg-gray-100 text-gray-700',
    'overdue'     => 'bg-red-100 text-red-800',
    'available'   => 'bg-green-100 text-green-800',
    'on_duty'     => 'bg-purple-100 text-purple-800',
    'off'         => 'bg-gray-100 text-gray-700',
    'maintenance' => 'bg-orange-100 text-orange-800',
    'inactive'    => 'bg-gray-100 text-gray-600',
    'active'      => 'bg-green-100 text-green-800',
    'prospect'    => 'bg-blue-100 text-blue-800',
    'scheduled'   => 'bg-yellow-100 text-yellow-800',
    'in_progress' => 'bg-blue-100 text-blue-800',
];
$cls = $colors[$status] ?? 'bg-gray-100 text-gray-700';
$label = ucfirst(str_replace('_', ' ', $status));
@endphp

@if($link)
    <a href="{{ $link }}" class="inline-block {{ $cls }} px-2.5 py-0.5 rounded-full text-xs font-semibold hover:opacity-75 transition-opacity">{{ $label }}</a>
@else
    <span class="inline-block {{ $cls }} px-2.5 py-0.5 rounded-full text-xs font-semibold">{{ $label }}</span>
@endif
