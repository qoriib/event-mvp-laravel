<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'EventLink') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-950 text-gray-100 min-h-screen">
        <header class="border-b border-gray-800 bg-gray-900/80 backdrop-blur">
            <nav class="container mx-auto flex flex-wrap items-center justify-between gap-4 px-5 py-4">
                <a href="{{ route('home') }}" class="text-xl font-semibold text-indigo-400">{{ config('app.name', 'EventLink') }}</a>
                <div class="flex items-center gap-3 text-sm">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-white' : 'text-gray-300 hover:text-white' }}">Home</a>
                    <a href="{{ route('events.index') }}" class="{{ request()->routeIs('events.*') ? 'text-white' : 'text-gray-300 hover:text-white' }}">Events</a>
                    @auth
                        @if(auth()->user()->role === \App\Models\User::ROLE_ORGANIZER)
                            <a href="{{ route('organizer.dashboard') }}" class="{{ request()->routeIs('organizer.*') ? 'text-white' : 'text-gray-300 hover:text-white' }}">Organize</a>
                            <a href="{{ route('organizer.dashboard') }}#profile" class="{{ request()->routeIs('organizer.*') ? 'text-white' : 'text-gray-300 hover:text-white' }}">Profile</a>
                        @else
                            <a href="{{ route('customer.dashboard') }}#profile" class="{{ request()->routeIs('customer.*') ? 'text-white' : 'text-gray-300 hover:text-white' }}">Profile</a>
                        @endif
                        <form action="{{ route('logout') }}" method="POST" class="m-0 inline">
                            @csrf
                            <button type="submit" class="rounded-full border border-gray-700 px-3 py-1 text-gray-300 hover:border-red-400 hover:text-red-200">
                                Logout
                            </button>
                        </form>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'text-white' : 'text-gray-300 hover:text-white' }}">Login</a>
                        <a href="{{ route('register') }}" class="rounded-full border border-indigo-500/50 bg-indigo-500/10 px-3 py-1 text-indigo-300 hover:bg-indigo-500/20">
                            Register
                        </a>
                    @endguest
                </div>
            </nav>
        </header>
        <main class="container mx-auto space-y-12 px-5 py-12">
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('info'))
                <div class="rounded-2xl border border-indigo-500/30 bg-indigo-500/10 px-4 py-3 text-sm text-indigo-100">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('error'))
                <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-2xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-100">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
        <footer class="border-t border-gray-800 bg-gray-900/90">
            <div class="container mx-auto flex flex-col gap-4 px-5 py-6 text-sm text-gray-400 md:flex-row md:items-center md:justify-between">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'EventLink') }}. All rights reserved.</span>
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-white">Terms</a>
                    <a href="#" class="hover:text-white">Privacy</a>
                    <a href="#" class="hover:text-white">Support</a>
                </div>
            </div>
        </footer>
        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    </body>
</html>
