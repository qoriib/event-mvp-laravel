@extends('layouts.app')

@section('content')
@php
    $formatCurrency = fn (int $value) => 'Rp '.number_format($value, 0, ',', '.');
    $priceLabel = static function ($event) use ($formatCurrency) {
        $prices = collect($event->ticketTypes ?? [])
            ->pluck('priceIDR')
            ->filter(fn ($value) => is_numeric($value))
            ->sort()
            ->values();

        if ($prices->isEmpty()) {
            return $event->isPaid ? 'Harga akan diumumkan' : 'Gratis';
        }

        $min = $prices->first();
        $max = $prices->last();

        if ($min === 0) {
            return 'Gratis';
        }

        return $min === $max
            ? $formatCurrency($min)
            : $formatCurrency($min).' – '.$formatCurrency($max);
    };

    $formatDateRange = static function ($start, $end) {
        if (! $start) {
            return '-';
        }

        $startAt = \Illuminate\Support\Carbon::parse($start);
        $endAt = $end ? \Illuminate\Support\Carbon::parse($end) : null;

        if (! $endAt || $startAt->isSameDay($endAt)) {
            return $startAt->translatedFormat('d M Y');
        }

        return $startAt->translatedFormat('d M Y').' – '.$endAt->translatedFormat('d M Y');
    };
@endphp

<section class="space-y-8">
    <div class="text-center space-y-3">
        <h1 class="text-3xl font-semibold text-white md:text-4xl">
            Jelajahi Event Musik & Festival
        </h1>
        <p class="mx-auto max-w-2xl text-gray-400">
            Cari acara favorit, filter berdasarkan kategori dan lokasi, lalu amankan tiketmu.
            Kami selalu menambahkan agenda terbaru setiap minggu.
        </p>
    </div>

    <form action="{{ route('events.index') }}" method="GET" class="grid gap-3 rounded-2xl border border-gray-700 bg-gray-900/70 p-5 md:grid-cols-[2fr,1fr,1fr] md:items-center">
        <input
            type="search"
            name="search"
            value="{{ $filters['search'] }}"
            placeholder="Cari event berdasarkan judul, lokasi, atau deskripsi..."
            class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        />
        <select name="category" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">{{ $filters['category'] ? 'Semua Kategori' : 'Semua Kategori' }}</option>
            @foreach ($categoryOptions as $category)
                <option value="{{ $category }}" @selected($filters['category'] === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="location" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            <option value="">{{ $filters['location'] ? 'Semua Lokasi' : 'Semua Lokasi' }}</option>
            @foreach ($locationOptions as $option)
                <option value="{{ $option }}" @selected($filters['location'] === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <div class="md:col-span-3 flex flex-col gap-3 sm:flex-row sm:justify-between">
            <p class="text-sm text-gray-400">
                Menampilkan <span class="font-semibold text-indigo-200">{{ $events->total() }}</span> event ditemukan.
            </p>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                    Terapkan Filter
                </button>
                <a href="{{ route('events.index') }}" class="rounded-lg border border-gray-700 px-4 py-2 text-sm font-semibold text-gray-200 hover:border-indigo-400 hover:text-white">
                    Reset
                </a>
            </div>
        </div>
    </form>

    @if ($events->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-10 text-center text-gray-400">
            Tidak ditemukan event dengan kriteria yang dipilih. Coba kata kunci atau filter berbeda.
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($events as $event)
                <article class="flex h-full flex-col gap-4 rounded-2xl border border-gray-800 bg-gray-900/70 p-6 transition hover:border-indigo-400/40">
                    <header class="flex items-center justify-between text-xs text-gray-400">
                        <span class="flex items-center gap-2">📅 {{ $formatDateRange($event->startAt, $event->endAt) }}</span>
                        <span class="flex items-center gap-2">📍 {{ $event->location }}</span>
                    </header>
                    @if ($event->category)
                        <span class="self-start rounded-full border border-indigo-500/30 bg-indigo-500/10 px-3 py-1 text-xs uppercase tracking-wide text-indigo-200">
                            {{ $event->category }}
                        </span>
                    @endif
                    <h3 class="text-lg font-semibold text-white">{{ $event->title }}</h3>
                    <p class="text-sm text-gray-400 line-clamp-3">
                        {{ $event->description ?? 'Belum ada deskripsi event.' }}
                    </p>
                    <footer class="mt-auto flex items-center justify-between text-sm text-indigo-100">
                        <span>{{ $priceLabel($event) }}</span>
                        <a href="{{ route('events.show', $event->id) }}" class="rounded-lg border border-indigo-500/40 px-4 py-1 text-xs font-semibold text-indigo-100 hover:bg-indigo-500/20">
                            Lihat Detail
                        </a>
                    </footer>
                </article>
            @endforeach
        </div>

        <div class="pt-8">
            {{ $events->links('pagination::tailwind') }}
        </div>
    @endif
</section>
@endsection
