@props(['label' => '', 'value' => '0', 'tone' => 'blue'])

@php
    $tones = [
        'blue' => 'from-blue-500/20 to-blue-900/20 text-blue-300 border-blue-500/20',
        'green' => 'from-emerald-500/20 to-emerald-900/20 text-emerald-300 border-emerald-500/20',
        'yellow' => 'from-amber-500/20 to-amber-900/20 text-amber-300 border-amber-500/20',
        'red' => 'from-red-500/20 to-red-900/20 text-red-300 border-red-500/20',
        'purple' => 'from-violet-500/20 to-violet-900/20 text-violet-300 border-violet-500/20',
        'gray' => 'from-slate-500/20 to-slate-900/20 text-slate-300 border-slate-500/20',
    ];
@endphp

<div class="rounded-2xl border bg-gradient-to-br {{ $tones[$tone] ?? $tones['blue'] }} p-5 shadow-sm">
    <div class="text-xs uppercase tracking-widest opacity-80">{{ $label }}</div>
    <div class="mt-3 text-4xl font-black text-white">{{ $value }}</div>
</div>
