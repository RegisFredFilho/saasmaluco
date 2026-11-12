@props([
    'title',
    'lead' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        <h1 class="sg-page-title">{{ $title }}</h1>
        @if ($lead)
            <p class="sg-page-lead">{{ $lead }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
