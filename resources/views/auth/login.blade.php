@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<section class="space-y-6 rounded-2xl border border-gray-800 bg-gray-900/80 p-8 shadow-lg">
    <header class="space-y-1 text-center">
        <h1 class="text-2xl font-semibold text-white">Masuk</h1>
        <p class="text-sm text-gray-400">Gunakan email dan kata sandi untuk melanjutkan.</p>
    </header>

    <form action="{{ route('login.store') }}" method="POST" class="space-y-4">
        @csrf
        <div class="space-y-2">
            <label for="email" class="text-sm font-medium text-gray-200">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                placeholder="kamu@mail.com"
            />
            @error('email')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="password" class="text-sm font-medium text-gray-200">Kata Sandi</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                placeholder="••••••••"
            />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-300">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-600 bg-gray-900 text-indigo-500 focus:ring-indigo-500" />
                Ingat saya
            </label>
            <a href="#" class="text-indigo-300 hover:text-indigo-100">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400">
            Masuk
        </button>
    </form>

    <p class="text-center text-sm text-gray-400">
        Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-300 hover:text-indigo-100">Daftar sekarang</a>
    </p>
</section>
@endsection
