@extends('layouts.app')

@section('content')
@php
    $statusLabels = [
        \App\Models\Transaction::STATUS_WAITING_PAYMENT => 'Menunggu Pembayaran',
        \App\Models\Transaction::STATUS_WAITING_CONFIRMATION => 'Menunggu Konfirmasi',
        \App\Models\Transaction::STATUS_DONE => 'Selesai',
        \App\Models\Transaction::STATUS_REJECTED => 'Ditolak',
        \App\Models\Transaction::STATUS_EXPIRED => 'Kedaluwarsa',
        \App\Models\Transaction::STATUS_CANCELED => 'Dibatalkan',
    ];
@endphp

<section class="space-y-10">
    <header class="space-y-2">
        <h1 class="text-3xl font-semibold text-white">Profil &amp; Tiket Saya</h1>
        <p class="text-sm text-gray-400">
            Perbarui biodata kamu dan pantau seluruh transaksi yang pernah dilakukan.
        </p>
    </header>

    <section class="grid gap-6 md:grid-cols-[1.2fr,0.8fr]" id="profile">
        <form action="{{ route('customer.profile.update') }}" method="POST" class="space-y-4 rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
            @csrf
            <h2 class="text-xl font-semibold text-white">Biodata</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="name" class="text-sm font-medium text-gray-200">Nama Lengkap</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium text-gray-200">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
            </div>
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium text-gray-200">Kata Sandi (opsional)</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    placeholder="Isi jika ingin mengganti kata sandi"
                />
            </div>
            <button type="submit" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                Simpan Perubahan
            </button>
        </form>

        <div class="grid gap-4">
            <div class="rounded-2xl border border-indigo-500/20 bg-indigo-500/10 p-6">
                <p class="text-sm text-indigo-200">Status Akun</p>
                <p class="mt-2 text-xl font-semibold text-white">Customer</p>
                <p class="mt-4 text-xs text-indigo-100/80">Gunakan akun organizer untuk membuat dan mengelola event.</p>
            </div>
            <div class="rounded-2xl border border-gray-700 bg-gray-900/70 p-6">
                <p class="text-sm text-gray-400">Total Transaksi</p>
                <p class="mt-2 text-3xl font-semibold text-white">{{ $transactions->total() }}</p>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-white">Riwayat Transaksi</h2>
            <a href="#" class="rounded-lg border border-indigo-500/40 px-4 py-2 text-sm font-semibold text-indigo-100 hover:bg-indigo-500/20">
                Klaim Event Gratis
            </a>
        </div>

        @if($transactions->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                Kamu belum memiliki transaksi. Jelajahi event dan dapatkan tiket pertamamu!
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border border-gray-800 bg-gray-900/70">
                <table class="min-w-full divide-y divide-gray-800 text-sm text-gray-200">
                    <thead class="bg-gray-900/80 text-xs uppercase tracking-wide text-gray-400">
                        <tr>
                            <th class="px-4 py-3 text-left">Event</th>
                            <th class="px-4 py-3 text-left">Tiket</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Dibuat</th>
                            <th class="px-4 py-3 text-left">Review</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @foreach($transactions as $transaction)
                            <tr>
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-medium text-white">{{ $transaction->event->title ?? '-' }}</p>
                                        <p class="text-xs text-gray-400">{{ $transaction->event?->location }}</p>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-xs text-gray-300">
                                        {{ $transaction->items->pluck('ticketType.name')->implode(', ') ?: '-' }}
                                    </p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-gray-800/60 px-2 py-1 text-xs uppercase tracking-wide text-indigo-200">
                                        {{ $statusLabels[$transaction->status] ?? str_replace('_', ' ', $transaction->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-400">
                                    {{ $transaction->created_at?->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-300">
                                    @php
                                        $existingReview = optional($transaction->event)->reviews->first();
                                    @endphp

                                    @if($transaction->status === \App\Models\Transaction::STATUS_DONE)
                                        @if($existingReview)
                                            <div class="space-y-1">
                                                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/10 px-2 py-1 text-indigo-200">
                                                    Rating {{ $existingReview->rating }}/5
                                                </span>
                                                @if($existingReview->comment)
                                                    <p class="text-xs text-gray-400 line-clamp-2">{{ $existingReview->comment }}</p>
                                                @endif
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                data-modal-target="review-modal-{{ $transaction->id }}"
                                                data-modal-toggle="review-modal-{{ $transaction->id }}"
                                                class="rounded-lg border border-indigo-500/40 px-3 py-1 font-semibold text-indigo-100 hover:bg-indigo-500/20"
                                            >
                                                Tulis Review
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @foreach($transactions as $transaction)
                @php
                    $existingReview = optional($transaction->event)->reviews->first();
                @endphp
                @if($transaction->status === \App\Models\Transaction::STATUS_DONE && ! $existingReview)
                    <div id="review-modal-{{ $transaction->id }}" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-full w-full flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/70 p-4 md:inset-0">
                        <div class="relative mx-auto w-full max-w-lg">
                            <div class="relative space-y-4 rounded-3xl border border-gray-800 bg-gray-950/90 p-6 shadow-xl">
                                <button
                                    type="button"
                                    class="absolute right-4 top-4 text-gray-400 hover:text-white"
                                    data-modal-hide="review-modal-{{ $transaction->id }}"
                                >
                                    <span class="sr-only">Tutup</span>
                                    &times;
                                </button>
                                <header class="space-y-1 pr-6">
                                    <p class="text-xs uppercase tracking-wide text-indigo-200">Tulis Review</p>
                                    <h3 class="text-lg font-semibold text-white">
                                        {{ $transaction->event->title ?? 'Event' }}
                                    </h3>
                                    <p class="text-xs text-gray-400">
                                        Beri penilaian setelah transaksi selesai.
                                    </p>
                                </header>
                                <form action="{{ route('events.reviews.store', $transaction->eventId) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div class="space-y-2">
                                        <label for="rating-{{ $transaction->id }}" class="text-sm font-medium text-gray-200">
                                            Rating
                                        </label>
                                        <select
                                            id="rating-{{ $transaction->id }}"
                                            name="rating"
                                            required
                                            class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        >
                                            @for($i = 5; $i >= 1; $i--)
                                                <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }} - {{ $i === 5 ? 'Luar biasa' : ($i === 4 ? 'Bagus' : ($i === 3 ? 'Cukup' : ($i === 2 ? 'Kurang' : 'Buruk'))) }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="space-y-2">
                                        <label for="comment-{{ $transaction->id }}" class="text-sm font-medium text-gray-200">
                                            Komentar (opsional)
                                        </label>
                                        <textarea
                                            id="comment-{{ $transaction->id }}"
                                            name="comment"
                                            rows="4"
                                            class="w-full rounded-lg border border-gray-700 bg-gray-900 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                            placeholder="Bagikan pengalamanmu mengikuti event ini."
                                        >{{ old('comment') }}</textarea>
                                    </div>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                        <button
                                            type="button"
                                            data-modal-hide="review-modal-{{ $transaction->id }}"
                                            class="rounded-lg border border-gray-700 px-4 py-2 text-sm font-semibold text-gray-200 hover:border-gray-500"
                                        >
                                            Batal
                                        </button>
                                        <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                                            Simpan Review
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach

            <div class="pt-6">
                {{ $transactions->links('pagination::tailwind') }}
            </div>
        @endif
    </section>
</section>
@endsection
