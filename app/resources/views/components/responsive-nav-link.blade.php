@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-accent bg-accent-soft pe-4 ps-3 py-2 text-start text-base font-medium text-accent-fg transition duration-150 ease-in-out focus:border-accent-hover focus:bg-accent-soft focus:text-accent-fg focus:outline-none'
            : 'block w-full border-l-4 border-transparent pe-4 ps-3 py-2 text-start text-base font-medium text-ink-muted transition duration-150 ease-in-out hover:border-canvas-line hover:bg-canvas hover:text-ink focus:border-canvas-line focus:bg-canvas focus:text-ink focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
