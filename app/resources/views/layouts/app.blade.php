<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@isset($title){{ $title }} · @endisset{{ config('app.name', 'SindiGig') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=syne:500,600,700|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="sg-shell font-sans">
    <div
        x-data="{
            sidebarOpen: false,
            collapsed: localStorage.getItem('sg-sidebar-collapsed') === '1',
            toggleCollapsed() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sg-sidebar-collapsed', this.collapsed ? '1' : '0');
            }
        }"
        class="min-h-screen md:grid"
        :class="collapsed ? 'md:grid-cols-[4.5rem_1fr]' : 'md:grid-cols-[16rem_1fr]'"
    >
        <div class="hidden md:block">
            <div class="sticky top-0 h-screen">
                @include('layouts.partials.sidebar')
            </div>
        </div>

        <div
            x-cloak
            x-show="sidebarOpen"
            class="fixed inset-0 z-40 md:hidden"
            @keydown.escape.window="sidebarOpen = false"
        >
            <div class="absolute inset-0 bg-black/70" @click="sidebarOpen = false"></div>
            <div class="absolute inset-y-0 left-0 w-72 max-w-[85vw] shadow-panel" @click.stop>
                @include('layouts.partials.sidebar', ['forceExpanded' => true])
            </div>
        </div>

        <div class="flex min-w-0 flex-col pb-20 md:pb-0">
            @include('layouts.partials.topbar')

            @isset($header)
                <div class="border-b border-canvas-line bg-canvas-elev/40">
                    <div class="sg-main !py-5">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <main class="relative flex-1">
                <div class="pointer-events-none absolute inset-0 bg-stage-radial opacity-70"></div>
                <div class="sg-main relative">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @include('layouts.partials.mobile-nav')
    </div>
</body>
</html>
