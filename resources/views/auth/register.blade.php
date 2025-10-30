@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-xl space-y-6 rounded-3xl border border-gray-800 bg-gray-900/70 p-8 shadow-xl">
    <div class="space-y-2 text-center">
        <h1 class="text-2xl font-semibold text-white">Daftar Eventify</h1>
        <p class="text-sm text-gray-400">Bergabung sebagai peserta atau organizer untuk merancang pengalaman terbaik.</p>
    </div>

    <form action="{{ route('register.store') }}" method="POST" class="space-y-5">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label for="name" class="text-sm font-medium text-gray-200">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="Nama kamu"
                />
                @error('name')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium text-gray-200">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="kamu@mail.com"
                />
                @error('email')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-gray-200">Kata Sandi</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="Minimal 6 karakter"
                />
                @error('password')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="space-y-2">
                <label for="password_confirmation" class="text-sm font-medium text-gray-200">Konfirmasi Kata Sandi</label>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="Ulangi kata sandi"
                />
            </div>
        </div>

        <div class="space-y-2">
            <span class="text-sm font-medium text-gray-200">Daftar sebagai</span>
            <div class="grid gap-3 md:grid-cols-2">
                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-700 bg-gray-950/60 p-4 text-sm text-gray-200 hover:border-indigo-400">
                    <input type="radio" name="role" value="{{ \App\Models\User::ROLE_CUSTOMER }}" {{ old('role', \App\Models\User::ROLE_CUSTOMER) === \App\Models\User::ROLE_CUSTOMER ? 'checked' : '' }} class="text-indigo-500 focus:ring-indigo-500" />
                    <div>
                        <p class="font-semibold text-white">Customer</p>
                        <p class="text-xs text-gray-400">Cari dan beli tiket event favorit kamu.</p>
                    </div>
                </label>
                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-gray-700 bg-gray-950/60 p-4 text-sm text-gray-200 hover:border-indigo-400">
                    <input type="radio" name="role" value="{{ \App\Models\User::ROLE_ORGANIZER }}" {{ old('role') === \App\Models\User::ROLE_ORGANIZER ? 'checked' : '' }} class="text-indigo-500 focus:ring-indigo-500" />
                    <div>
                        <p class="font-semibold text-white">Organizer</p>
                        <p class="text-xs text-gray-400">Kelola event, tiket, dan promosi dari dashboard.</p>
                    </div>
                </label>
            </div>
            @error('role')
                <p class="text-xs text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="w-full rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
            Buat Akun
        </button>
    </form>

    <p class="text-center text-sm text-gray-400">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-300 hover:text-indigo-100">Masuk sekarang</a>
    </p>
</section>
@endsection
