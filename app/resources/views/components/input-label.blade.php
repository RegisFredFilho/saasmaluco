@props(['value' => null])

<label {{ $attributes->merge(['class' => 'sg-label']) }}>
    {{ $value ?? $slot }}
</label>
