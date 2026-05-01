<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Symflow Orders' }}</title>
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full font-sans text-zinc-900 antialiased">
<div class="min-h-full">
    <header class="sticky top-0 z-30 border-b border-zinc-200/80 bg-white/80 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2.5">
                <span class="grid size-9 place-items-center rounded-lg bg-orange-500/10 ring-1 ring-orange-600/20">
                    <svg class="size-5 text-orange-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
                    </svg>
                </span>
                <span class="flex flex-col leading-tight">
                    <span class="text-sm font-semibold tracking-tight">Symflow Orders</span>
                    <span class="text-[11px] font-medium uppercase tracking-wider text-zinc-500">multi-workflow demo</span>
                </span>
            </a>
            <livewire:components.role-switcher />
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-6 py-8">
        @if (session('flash.success'))
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('flash.success') }}</div>
        @endif
        @if (session('flash.error'))
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('flash.error') }}</div>
        @endif

        {{ $slot }}
    </main>

    <footer class="border-t border-zinc-200/80 bg-white/50">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-6 py-6 text-xs text-zinc-500">
            <div>
                Showcase for
                <a href="https://github.com/vandetho/symflow-laravel" class="font-medium text-zinc-700 hover:text-orange-700">vandetho/symflow-laravel</a>
                — Symfony-compatible workflow engine for Laravel.
            </div>
            <div class="flex items-center gap-4">
                <a href="https://symflowbuilder.com" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 font-medium text-zinc-700 hover:text-orange-700">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><line x1="10" y1="6.5" x2="14" y2="6.5"/><line x1="6.5" y1="10" x2="6.5" y2="14"/></svg>
                    Design workflows on symflowbuilder.com
                </a>
            </div>
        </div>
    </footer>
</div>

@livewireScripts
</body>
</html>
