@extends('layouts.app')

@section('content')
@php
    $formatCurrency = fn (int $value) => 'Rp '.number_format($value, 0, ',', '.');
    $formatDate = static function ($date) {
        return $date ? \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y H:i') : '-';
    };
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
    <header class="grid gap-6 rounded-3xl border border-gray-800 bg-gray-900/70 p-8 lg:grid-cols-[1.2fr_0.8fr]">
        <div class="space-y-5">
            <span class="inline-flex items-center rounded-full border border-indigo-500/30 bg-indigo-500/10 px-3 py-1 text-xs uppercase tracking-wide text-indigo-200">
                {{ $event->category ?? 'Event' }}
            </span>
            <div class="space-y-2">
                <h1 class="text-3xl font-semibold text-white md:text-4xl">{{ $event->title }}</h1>
                <p class="text-sm text-gray-400">
                    Diselenggarakan oleh
                    @if($event->organizer)
                        <a href="{{ route('organizers.show', $event->organizer->id) }}" class="text-indigo-200 hover:text-indigo-100">
                            {{ $event->organizer->displayName }}
                        </a>
                    @else
                        <span class="text-indigo-200">-</span>
                    @endif
                </p>
            </div>
            <div class="grid gap-3 text-sm text-gray-300 sm:grid-cols-2">
                <div class="rounded-2xl border border-gray-800 bg-gray-950/60 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Lokasi</p>
                    <p class="mt-1 font-medium text-white">{{ $event->location }}</p>
                </div>
                <div class="rounded-2xl border border-gray-800 bg-gray-950/60 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Waktu</p>
                    <p class="mt-1 font-medium text-white">{{ $formatDate($event->startAt) }} &ndash; {{ $formatDate($event->endAt) }}</p>
                </div>
            </div>
            <p class="text-sm leading-relaxed text-gray-300">{{ $event->description ?? 'Belum ada deskripsi event.' }}</p>
        </div>
        <aside class="space-y-4 rounded-2xl border border-gray-800 bg-gray-950/60 p-6">
            <h2 class="text-lg font-semibold text-white">Jenis Tiket</h2>
            <ul class="space-y-3 text-sm text-gray-300">
                @forelse($event->ticketTypes as $ticketType)
                    <li class="flex items-center justify-between rounded-xl border border-gray-800 bg-gray-900/70 px-4 py-3">
                        <div>
                            <p class="font-medium text-white">{{ $ticketType->name }}</p>
                            <p class="text-xs text-gray-400">Quota: {{ $ticketType->quota ?? 'Tidak terbatas' }}</p>
                        </div>
                        <span class="font-semibold text-indigo-200">{{ $formatCurrency((int) $ticketType->priceIDR) }}</span>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-gray-700 bg-gray-900/40 px-4 py-3 text-center text-gray-400">Belum ada jenis tiket.</li>
                @endforelse
            </ul>
        </aside>
    </header>

    @guest
        <section class="rounded-3xl border border-dashed border-gray-700 bg-gray-900/40 p-6 text-center text-gray-300">
            <p>
                Ingin membeli tiket? <a href="{{ route('login') }}" class="text-indigo-300 hover:text-indigo-100">Masuk</a> atau
                <a href="{{ route('register') }}" class="text-indigo-300 hover:text-indigo-100">daftar</a> terlebih dahulu.
            </p>
        </section>
    @endguest

    @auth
        @if($user->role === \App\Models\User::ROLE_CUSTOMER)
            <section class="grid gap-6 rounded-3xl border border-gray-800 bg-gray-900/70 p-6 lg:grid-cols-[1.2fr_0.8fr]">
                <div class="space-y-4">
                    <h2 class="text-xl font-semibold text-white">Beli Tiket</h2>
                    <form action="{{ route('events.purchase', $event->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label for="ticketTypeId" class="text-sm font-medium text-gray-200">Pilih jenis tiket</label>
                            <select name="ticketTypeId" id="ticketTypeId" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="" disabled selected>-- Pilih tiket --</option>
                                @foreach($event->ticketTypes as $ticketType)
                                    <option value="{{ $ticketType->id }}">{{ $ticketType->name }} &bull; {{ $formatCurrency((int) $ticketType->priceIDR) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label for="qty" class="text-sm font-medium text-gray-200">Jumlah</label>
                            <input type="number" name="qty" id="qty" value="1" min="1" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-indigo-500 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-400">Checkout</button>
                    </form>
                </div>
                <div class="space-y-4 rounded-2xl border border-gray-800 bg-gray-950/60 p-5 text-sm text-gray-300">
                    <div class="space-y-2">
                        <h3 class="text-lg font-semibold text-white">Informasi Pembayaran</h3>
                        <p class="text-xs text-gray-400">
                            Transfer sesuai total pembayaran ke salah satu rekening berikut, lalu unggah bukti transfer maksimum 2 jam setelah transaksi dibuat.
                        </p>
                        <div class="space-y-2 text-xs text-gray-200">
                            <div class="rounded-xl border border-gray-800 bg-gray-900/70 p-3">
                                <p class="font-semibold text-white">Bank BCA</p>
                                <p>No. Rekening: <span class="font-mono">123-456-7890</span></p>
                                <p>a.n EventLink Indonesia</p>
                            </div>
                            <div class="rounded-xl border border-gray-800 bg-gray-900/70 p-3">
                                <p class="font-semibold text-white">Bank Mandiri</p>
                                <p>No. Rekening: <span class="font-mono">987-654-3210</span></p>
                                <p>a.n EventLink Indonesia</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs text-gray-400">
                        <p>Alur transaksi:</p>
                        <ul class="list-disc space-y-1 pl-5">
                            <li><strong>Waiting Payment</strong>: upload bukti transfer melalui form di bawah.</li>
                            <li><strong>Waiting Confirmation</strong>: penyelenggara memverifikasi (maks. 3 hari).</li>
                            <li><strong>Done</strong>: tiket digital diterbitkan dan dapat diunduh dari dashboard.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <section class="space-y-4" id="transactions">
                <h2 class="text-xl font-semibold text-white">Transaksi Saya</h2>
                @if($customerTransactions->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                        Belum ada transaksi untuk event ini.
                    </div>
                @else
                    <div class="grid gap-6 md:grid-cols-2">
                        @foreach($customerTransactions as $transaction)
                            <article class="space-y-3 rounded-2xl border border-gray-800 bg-gray-900/70 p-5 text-sm text-gray-300">
                                <header class="flex items-center justify-between">
                                    <span class="rounded-full bg-gray-800/60 px-3 py-1 text-xs uppercase tracking-wide text-indigo-200">
                                        {{ $statusLabels[$transaction->status] ?? $transaction->status }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $transaction->created_at?->diffForHumans() }}</span>
                                </header>
                                <div>
                                    <p class="text-xs text-gray-400">Total Dibayar</p>
                                    <p class="text-lg font-semibold text-white">{{ $formatCurrency((int) $transaction->totalPayableIDR) }}</p>
                                </div>
                                <ul class="list-disc space-y-1 pl-5 text-xs text-gray-400">
                                    @foreach($transaction->items as $item)
                                        <li>{{ $item->ticketType->name ?? 'Tiket' }} &times; {{ $item->qty }}</li>
                                    @endforeach
                                </ul>

                                @if($transaction->status === \App\Models\Transaction::STATUS_WAITING_PAYMENT)
                                    <div class="rounded-xl border border-indigo-500/20 bg-indigo-500/10 p-3 text-xs text-indigo-100">
                                        Transfer ke BCA 123-456-7890 a.n EventLink Indonesia atau Mandiri 987-654-3210 a.n EventLink Indonesia, lalu unggah bukti melalui form berikut.
                                    </div>
                                    <form action="{{ route('transactions.proof', $transaction->id) }}" method="POST" enctype="multipart/form-data" class="space-y-2">
                                        @csrf
                                        <label class="text-xs font-medium text-gray-200" for="paymentProof-{{ $transaction->id }}">Unggah bukti transfer (jpg, png, pdf maks. 2MB)</label>
                                        <input type="file" name="paymentProof" id="paymentProof-{{ $transaction->id }}" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-xs text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                                        <button type="submit" class="w-full rounded-lg bg-indigo-500 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-400">Kirim Bukti Pembayaran</button>
                                    </form>
                                @endif

                                @if($transaction->paymentProofUrl)
                                    <a href="{{ $transaction->paymentProofUrl }}" target="_blank" class="block text-xs text-indigo-300 hover:text-indigo-100">Lihat bukti pembayaran</a>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            @if($canReview)
                <section class="space-y-4 rounded-3xl border border-gray-800 bg-gray-900/70 p-6">
                    <header class="space-y-1">
                        <h2 class="text-xl font-semibold text-white">Bagikan Pengalamanmu</h2>
                        <p class="text-sm text-gray-400">
                            Beri ulasan untuk membantu peserta lain dan meningkatkan kualitas event ini.
                        </p>
                    </header>
                    <form action="{{ route('events.reviews.store', $event->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <label for="rating-event" class="text-sm font-medium text-gray-200">Rating</label>
                                <select
                                    id="rating-event"
                                    name="rating"
                                    required
                                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    @for($i = 5; $i >= 1; $i--)
                                        <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }} &mdash; {{ $i === 5 ? 'Luar biasa' : ($i === 4 ? 'Bagus' : ($i === 3 ? 'Cukup' : ($i === 2 ? 'Kurang' : 'Buruk'))) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label for="comment-event" class="text-sm font-medium text-gray-200">Komentar (opsional)</label>
                            <textarea
                                id="comment-event"
                                name="comment"
                                rows="4"
                                class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                placeholder="Bagaimana pengalamanmu menghadiri event ini?"
                            >{{ old('comment') }}</textarea>
                        </div>
                        <button type="submit" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                            Kirim Review
                        </button>
                    </form>
                </section>
            @elseif($userReview)
                <section class="space-y-3 rounded-3xl border border-gray-800 bg-gray-900/70 p-6">
                    <header class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-white">Ulasanmu</h2>
                        <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-sm text-indigo-200">Rating {{ $userReview->rating }}/5</span>
                    </header>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        {{ $userReview->comment ?? 'Kamu memberikan rating tanpa komentar.' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Dikirim {{ $userReview->created_at?->diffForHumans() }}
                    </p>
                </section>
            @endif
        @endif

        @if($user->role === \App\Models\User::ROLE_ORGANIZER && $isOrganizerOwner)
            <section class="space-y-4">
                <h2 class="text-xl font-semibold text-white">Manajemen Transaksi</h2>
                @if($managedTransactions->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                        Belum ada transaksi untuk event ini.
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-gray-800 bg-gray-900/70">
                        <table class="min-w-full divide-y divide-gray-800 text-sm text-gray-200">
                            <thead class="bg-gray-900/80 text-xs uppercase tracking-wide text-gray-400">
                                <tr>
                                    <th class="px-4 py-3 text-left">Peserta</th>
                                    <th class="px-4 py-3 text-left">Tiket</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                    <th class="px-4 py-3 text-left">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach($managedTransactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-white">{{ $transaction->user->name ?? 'Peserta' }}</p>
                                            <p class="text-xs text-gray-400">{{ $transaction->user->email ?? '-' }}</p>
                                            @if($transaction->paymentProofUrl)
                                                <a href="{{ $transaction->paymentProofUrl }}" target="_blank" class="mt-1 inline-block text-xs text-indigo-300 hover:text-indigo-100">Bukti pembayaran</a>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-300">
                                            {{ $transaction->items->pluck('ticketType.name')->implode(', ') ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="rounded-full bg-gray-800/60 px-2 py-1 text-xs uppercase tracking-wide text-indigo-200">
                                                {{ $statusLabels[$transaction->status] ?? $transaction->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <form action="{{ route('transactions.status', $transaction->id) }}" method="POST" class="flex flex-col gap-2 text-xs">
                                                @csrf
                                                <select name="status" class="rounded-lg border border-gray-700 bg-gray-950/60 px-2 py-1 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                    @foreach($statusOptions as $status)
                                                        <option value="{{ $status }}" @selected($transaction->status === $status)>
                                                            {{ $statusLabels[$status] ?? $status }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="self-start rounded-lg bg-indigo-500 px-3 py-1 font-semibold text-white hover:bg-indigo-400">
                                                    Update Status
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
    @endauth

    <section class="space-y-4">
        <h2 class="text-xl font-semibold text-white">Review Peserta</h2>
        @if($event->reviews->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                Belum ada review untuk event ini.
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($event->reviews->take(6) as $review)
                    <article class="rounded-2xl border border-gray-800 bg-gray-900/70 p-5 text-sm text-gray-300">
                        <header class="flex items-center justify-between">
                            <p class="font-semibold text-white">{{ $review->user->name ?? 'Peserta' }}</p>
                            <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs text-indigo-200">Rating {{ $review->rating }}/5</span>
                        </header>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">{{ $review->comment ?? 'Tanpa komentar.' }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ $review->created_at?->diffForHumans() }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</section>
@endsection
