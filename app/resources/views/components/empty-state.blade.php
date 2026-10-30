@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'sg-empty']) }}>
    @if ($title)
        <p class="mb-1 font-medium text-ink-soft">{{ $title }}</p>
    @endif
    <div class="text-ink-muted">{{ $slot }}</div>
</div>
