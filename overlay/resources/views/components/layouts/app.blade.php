<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
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
        <div class="flex h-full" x-data="{ sidebarOpen: false }">
            <x-ui.sidebar />

            <div class="flex flex-1 flex-col overflow-hidden">
                <x-ui.topbar />

                @if (isset($header))
                    <header class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-4">
                        <h1 class="text-lg font-semibold text-slate-900">{{ $header }}</h1>
                    </header>
                @endif

                <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    <x-flash-messages />
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
