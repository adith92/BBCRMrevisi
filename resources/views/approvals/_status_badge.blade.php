@php
$badges = [
    'pending'   => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'approved'  => 'bg-green-100 text-green-800 border-green-200',
    'rejected'  => 'bg-red-100 text-red-800 border-red-200',
    'escalated' => 'bg-blue-100 text-blue-800 border-blue-200',
];
$labels = [
    'pending'   => 'Menunggu',
    'approved'  => 'Disetujui',
    'rejected'  => 'Ditolak',
    'escalated' => 'Dieskalasi',
];
$dotColors = [
    'pending'   => 'bg-yellow-500',
    'approved'  => 'bg-green-500',
    'rejected'  => 'bg-red-500',
    'escalated' => 'bg-blue-500',
];
$cls   = $badges[$status]   ?? 'bg-gray-100 text-gray-700 border-gray-200';
$label = $labels[$status]   ?? ucfirst($status);
$dot   = $dotColors[$status] ?? 'bg-gray-400';
@endphp
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $cls }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>
