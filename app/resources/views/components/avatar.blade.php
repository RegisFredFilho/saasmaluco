@props([
    'src' => null,
    'name' => '',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-10 w-10 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-lg',
        '2xl' => 'h-28 w-28 text-2xl',
    ];
    $box = $sizes[$size] ?? $sizes['md'];
    $initial = strtoupper(mb_substr(trim((string) $name) !== '' ? (string) $name : '?', 0, 1));
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" {{ $attributes->merge(['class' => $box.' shrink-0 rounded-full object-cover']) }}>
@else
    <span {{ $attributes->merge(['class' => 'flex '.$box.' shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-accent to-vibe font-semibold text-white']) }}>
        {{ $initial }}
    </span>
@endif
