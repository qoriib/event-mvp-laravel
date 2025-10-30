@extends('layouts.app')

@section('content')
@php
    $formatDate = static function ($date) {
        return $date ? \Illuminate\Support\Carbon::parse($date)->translatedFormat('d M Y H:i') : '-';
    };
    $formatCurrency = fn (int $value) => 'Rp '.number_format($value, 0, ',', '.');
@endphp

<section class="space-y-10">
    <header class="space-y-4 rounded-3xl border border-gray-800 bg-gray-900/70 p-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="space-y-2">
                <h1 class="text-3xl font-semibold text-white md:text-4xl">{{ $organizer->displayName }}</h1>
                <p class="text-sm text-gray-400">
                    Dikelola oleh <span class="text-indigo-300">{{ $organizer->user->name }}</span> &middot;
                    <span class="text-gray-500">{{ $organizer->user->email }}</span>
                </p>
            </div>
            <div class="rounded-2xl border border-indigo-500/30 bg-indigo-500/10 px-6 py-4 text-center">
                <p class="text-xs uppercase tracking-wide text-indigo-200">Rating</p>
                <p class="text-3xl font-semibold text-white">
                    {{ number_format($organizer->ratingsAvg, 2) }}
                </p>
                <p class="text-xs text-indigo-100/80">{{ $organizer->ratingsCount }} ulasan</p>
            </div>
        </div>
        <p class="text-sm leading-relaxed text-gray-300">{{ $organizer->bio ?: 'Organizer ini belum menambahkan deskripsi.' }}</p>
    </header>

    <section class="space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-white">Event oleh {{ $organizer->displayName }}</h2>
            <span class="text-sm text-gray-400">Total {{ $events->total() }} event</span>
        </div>

        @if($events->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                Organizer ini belum memiliki event aktif.
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach($events as $event)
                    <article class="flex h-full flex-col gap-3 rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
                        <header class="space-y-1">
                            <span class="text-xs uppercase tracking-wide text-indigo-200">
                                {{ $formatDate($event->startAt) }}
                                @if ($event->location)
                                    • {{ $event->location }}
                                @endif
                            </span>
                            <h3 class="text-lg font-semibold text-white">{{ $event->title }}</h3>
                        </header>
                        <p class="text-sm text-gray-400 line-clamp-3">
                            {{ $event->description ?? 'Belum ada deskripsi event.' }}
                        </p>
                        <div class="grid gap-1 text-xs text-gray-400">
                            <span>Jenis tiket: {{ $event->ticketTypes->count() }}</span>
                            <span>Kuota kursi: {{ number_format($event->capacity) }}</span>
                            <span>Tersisa: {{ number_format($event->seatsAvailable) }}</span>
                        </div>
                        <footer class="mt-auto flex items-center justify-between text-sm text-indigo-100">
                            <span>{{ $event->ticketTypes->count() ? $formatCurrency((int) $event->ticketTypes->min('priceIDR')) : 'Harga belum tersedia' }}</span>
                            <a href="{{ route('events.show', $event->id) }}" class="rounded-lg border border-indigo-500/40 px-3 py-1 text-xs font-semibold text-indigo-100 hover:bg-indigo-500/20">
                                Lihat Event
                            </a>
                        </footer>
                    </article>
                @endforeach
            </div>
            <div class="pt-4">
                {{ $events->links('pagination::tailwind') }}
            </div>
        @endif
    </section>

    @if($reviews->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-2xl font-semibold text-white">Ulasan Peserta</h2>
            <div class="grid gap-4 md:grid-cols-2">
                @foreach($reviews as $review)
                    <article class="rounded-2xl border border-gray-800 bg-gray-900/70 p-5 text-sm text-gray-300">
                        <header class="flex items-center justify-between">
                            <p class="font-semibold text-white">{{ $review->user->name ?? 'Peserta' }}</p>
                            <span class="rounded-full bg-indigo-500/10 px-3 py-1 text-xs text-indigo-200">
                                Rating {{ $review->rating }}/5
                            </span>
                        </header>
                        <p class="mt-3 text-sm text-gray-400 leading-relaxed">{{ $review->comment ?? 'Tanpa komentar.' }}</p>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $review->event->title ?? '-' }} &middot; {{ $review->created_at?->diffForHumans() }}
                        </p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</section>
@endsection
