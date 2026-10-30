@props([
    'label' => null,
    'variant' => 'stage',
    'ratio' => 'aspect-[16/10]',
])

@php
$gradients = [
    'stage' => 'from-accent/40 via-canvas-elev to-vibe/30',
    'artist' => 'from-vibe/35 via-canvas-elev to-accent/25',
    'venue' => 'from-accent/25 via-canvas-mist to-canvas-elev',
    'opportunity' => 'from-amber-500/25 via-canvas-elev to-accent/20',
];
$g = $gradients[$variant] ?? $gradients['stage'];
@endphp

<div {{ $attributes->merge(['class' => "sg-media {$ratio} bg-gradient-to-br {$g}"]) }}>
    <div class="absolute inset-0 opacity-40" style="background-image: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.12), transparent 45%), radial-gradient(circle at 80% 70%, rgba(79,124,255,0.2), transparent 40%);"></div>
    <div class="relative flex h-full items-end p-4">
        @if ($label)
            <span class="font-display text-lg font-semibold text-ink/90">{{ $label }}</span>
        @else
            <x-icon name="music" class="h-8 w-8 text-ink/40" />
        @endif
    </div>
</div>
