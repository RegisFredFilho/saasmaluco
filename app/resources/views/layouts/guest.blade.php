<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SindiGig') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:500,600,700|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sg-shell font-sans">
    <div class="relative min-h-screen overflow-hidden">
        <x-stage-media variant="crowd" class="absolute inset-0 opacity-40" />
        <div class="absolute inset-0 bg-canvas/80"></div>
        <div class="pointer-events-none absolute inset-0 bg-stage-radial"></div>
        <div class="relative mx-auto flex min-h-screen max-w-md flex-col justify-center px-4 py-10">
            <a href="/" class="mb-8 flex items-center justify-center gap-2 font-display text-3xl font-semibold tracking-tight text-ink">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent text-base font-bold text-white shadow-glow">S</span>
                SindiGig
            </a>
            <div class="sg-panel p-6 md:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
