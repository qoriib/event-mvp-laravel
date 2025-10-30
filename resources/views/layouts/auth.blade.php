<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Eventify') }} &mdash; @yield('title', 'Masuk')</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_#312e81_0%,_#0f172a_45%,_#020617_100%)] text-gray-100">
        <main class="flex min-h-screen items-center justify-center px-5 py-12">
            <div class="w-full max-w-5xl space-y-8">
                <header class="text-center">
                    <a href="{{ route('home') }}" class="text-2xl font-semibold text-indigo-200">
                        {{ config('app.name', 'Eventify') }}
                    </a>
                    <p class="mt-2 text-sm text-indigo-100/70">
                        Platform event musik &amp; festival untuk organizer dan pengunjung.
                    </p>
                </header>

                @if(session('error'))
                    <div class="mx-auto max-w-md rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="mx-auto max-w-md rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="flex flex-col items-center gap-6 md:flex-row md:items-start md:justify-center md:gap-12">
                    <div class="hidden max-w-sm space-y-4 rounded-3xl border border-indigo-500/20 bg-indigo-500/10 p-6 text-sm text-indigo-100 md:block">
                        <p class="text-base font-semibold text-white">Kenapa Eventify?</p>
                        <ul class="list-disc space-y-2 pl-5">
                            <li>Kurasi event real-time sesuai minatmu.</li>
                            <li>Dashboard organizer komprehensif dan mudah digunakan.</li>
                            <li>Transaksi aman dengan verifikasi bukti transfer.</li>
                        </ul>
                        <p class="pt-2 text-xs text-indigo-100/80">
                            Bergabung sekarang dan rasakan pengalaman event yang lebih terarah.
                        </p>
                    </div>

                    <div class="w-full md:flex-1">
                        @yield('content')
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>
