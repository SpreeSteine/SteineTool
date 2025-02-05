<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @fluxStyles

    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">

        <flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <img src="{{ asset('logo.png') }}" alt="SteineTool" class="dark:hidden w-full" />

            <flux:input variant="filled" placeholder="Search..." icon="magnifying-glass" />


            <flux:navlist variant="outline">
                <flux:navlist.item icon="home" href="{{ route('main') }}" :current="request()->routeIs('main')">Home</flux:navlist.item>
                <flux:navlist.item icon="calculator" href="{{ route('tools.set-analyzer') }}" :current="request()->routeIs('tools.set-analyzer')">SetAnalyzer</flux:navlist.item>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="cog-6-tooth" href="{{ route('settings') }}" :current="request()->routeIs('settings')">Settings</flux:navlist.item>
                <flux:navlist.item icon="information-circle" href="#">Help</flux:navlist.item>
            </flux:navlist>

        </flux:sidebar>

        <flux:main>

            {{ $slot }}

        </flux:main>

        @fluxScripts

    </body>
</html>
