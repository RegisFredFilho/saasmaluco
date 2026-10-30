@props([
    'variant' => 'stage',
    'src' => null,
    'alt' => '',
    'class' => '',
])

@php
$stock = [
    'stage' => 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=1600&q=80',
    'crowd' => 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=1600&q=80',
    'artist' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?auto=format&fit=crop&w=1200&q=80',
    'dj' => 'https://images.unsplash.com/photo-1571330735066-03aaa9429d89?auto=format&fit=crop&w=1200&q=80',
    'studio' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=1200&q=80',
    'venue' => 'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=1200&q=80',
];
$url = $src ?: ($stock[$variant] ?? $stock['stage']);
@endphp

<div {{ $attributes->merge(['class' => 'sg-media '.$class]) }}>
    <img src="{{ $url }}" alt="{{ $alt }}" class="h-full w-full object-cover" loading="lazy">
    <div class="sg-media-overlay"></div>
</div>
