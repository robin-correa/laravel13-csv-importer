<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'CSV Importer') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <a href="{{ route('upload.create') }}" class="text-xl font-bold tracking-tight text-gray-900">
                    CSV Importer
                </a>
                <div class="flex items-center gap-6">
                    <a href="{{ route('upload.create') }}"
                       class="text-sm font-medium {{ request()->routeIs('upload.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900' }}">
                        Upload
                    </a>
                    <a href="{{ route('transactions.index') }}"
                       class="text-sm font-medium {{ request()->routeIs('transactions.*') ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-900' }}">
                        Transactions
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4">
                <div class="flex items-center">
                    <svg class="mr-3 h-5 w-5 shrink-0 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white mt-12">
        <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
            <p class="text-center text-xs text-gray-400">
                Built by <span class="font-medium text-gray-600">Robin Correa</span>
            </p>
        </div>
    </footer>
</body>
</html>
