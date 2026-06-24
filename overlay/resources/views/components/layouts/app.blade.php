<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-100">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' · ' : '' }}{{ config('app.name', 'ITFlow-Next') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="h-full font-sans antialiased text-slate-800">
        <x-ui.toast />

        <div class="flex h-full bg-slate-100" x-data="{ sidebarOpen: false }">
            <x-ui.sidebar />

            <div class="flex flex-1 flex-col overflow-hidden">
                <x-ui.topbar />

                @if (isset($header))
                    <header class="border-b border-slate-200/80 bg-white/90 px-4 py-5 backdrop-blur sm:px-6 lg:px-8">
                        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Workspace</p>
                                <h1 class="mt-1 text-xl font-semibold text-slate-950">{{ $header }}</h1>
                            </div>
                        </div>
                    </header>
                @endif

                <main class="flex-1 overflow-y-auto">
                    <div class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8">
                        <x-flash-messages />
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>
