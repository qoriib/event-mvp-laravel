@extends('layouts.app')

@section('content')
@php
    $heroStats = [
        ['label' => 'Event aktif', 'value' => '120+', 'description' => 'Konser & festival bulan ini'],
        ['label' => 'Organizer terpercaya', 'value' => '80+', 'description' => 'Partner resmi Eventify'],
        ['label' => 'Pengguna terdaftar', 'value' => '45K', 'description' => 'Komunitas penikmat event'],
    ];

    $featureCards = [
        [
            'title' => 'Kurasi Event Real-time',
            'description' => 'Dapatkan rekomendasi personal berdasarkan minat dan lokasi favoritmu. Notifikasi early bird langsung ke email.',
        ],
        [
            'title' => 'Dashboard Organizer Lengkap',
            'description' => 'Kelola agenda, kuota kursi, hingga laporan transaksi dari satu tempat. Tingkatkan penjualan dengan analitik praktis.',
        ],
        [
            'title' => 'Transaksi Aman & Transparan',
            'description' => 'Pembayaran terintegrasi, dukungan bukti transfer, dan validasi tiket otomatis saat peserta check-in.',
        ],
    ];

    $organizerSteps = [
        [
            'title' => 'Rancang event dengan template siap pakai',
            'description' => 'Pilih tipe acara, tambahkan jadwal, dan unggah cover dalam hitungan menit. Tidak perlu repot membuat dari nol.',
        ],
        [
            'title' => 'Aktifkan penjualan tiket & voucher',
            'description' => 'Buat kategori tiket, tetapkan harga, dan monitor kursi yang tersisa secara real-time dari dashboard organizer.',
        ],
        [
            'title' => 'Bangun reputasi dengan review peserta',
            'description' => 'Kumpulkan testimoni, kelola poin loyalti, dan gunakan masukan peserta untuk meningkatkan event berikutnya.',
        ],
    ];

    $testimonials = [
        [
            'quote' => 'Eventify mempermudah kami merilis lineup festival dalam satu malam. Penjualan tiket early bird naik 3x dibanding tahun lalu.',
            'name' => 'Arini Putri',
            'position' => 'Founder, Soundwave Productions',
        ],
        [
            'quote' => 'Sebagai penikmat konser, saya suka rekomendasi personalnya. Setiap minggu selalu ada agenda musik baru di kota saya.',
            'name' => 'Rizky Gunawan',
            'position' => 'Penggemar Musik Independen',
        ],
    ];

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

<section class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]">
    <div class="space-y-8">
        <span class="inline-flex items-center rounded-full border border-indigo-500/40 bg-indigo-500/10 px-4 py-1.5 text-xs font-medium uppercase tracking-wide text-indigo-200">
            Platform Event Musik & Festival #1
        </span>
        <div class="space-y-4">
            <h1 class="text-4xl font-semibold leading-tight text-white md:text-5xl">
                Temukan pengalaman live terbaik atau kelola event kamu dengan percaya diri.
            </h1>
            <p class="text-lg text-gray-300">
                Eventify menyatukan organizer dan penikmat event dalam satu platform. Soroti event kamu,
                atur penjualan tiket, dan hadirkan pengalaman tak terlupakan bagi audiens.
            </p>
        </div>

        <form action="{{ route('events.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <input
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari konser, festival, atau workshop..."
                class="w-full rounded-lg border border-gray-700 bg-gray-900/60 px-4 py-2 text-sm text-gray-100 placeholder:text-gray-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:flex-1"
            />
            <div class="flex gap-3">
                <button type="submit" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-400">
                    Temukan Event
                </button>
                <a href="{{ route('register') }}" class="rounded-lg border border-gray-700 px-5 py-2 text-sm font-semibold text-gray-200 hover:border-indigo-400 hover:text-white">
                    Jadi Organizer
                </a>
            </div>
        </form>

        <div class="flex flex-wrap gap-2 text-xs">
            @foreach ($categoryHints as $category)
                <span class="inline-flex items-center rounded-full border border-gray-700 bg-gray-900/60 px-3 py-1 uppercase text-gray-300">
                    #{{ $category }}
                </span>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($heroStats as $stat)
                <div class="rounded-2xl border border-gray-800 bg-gray-900/70 p-4">
                    <p class="text-sm text-gray-400">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold text-white">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stat['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-3xl border border-gray-800 bg-gray-900/80 p-6 shadow-xl">
        <div class="space-y-4">
            <p class="text-sm uppercase tracking-wide text-gray-400">
                Event Unggulan Minggu Ini
            </p>
            @if ($highlightedEvents->isEmpty())
                <p class="rounded-xl border border-dashed border-gray-700 bg-gray-900/60 p-6 text-center text-sm text-gray-400">
                    Belum ada event unggulan yang tersedia.
                </p>
            @else
                <div class="space-y-5">
                    @foreach ($highlightedEvents as $event)
                        <div class="rounded-2xl border border-indigo-500/10 bg-gradient-to-r from-indigo-950/50 via-gray-900 to-gray-900 p-5 transition hover:border-indigo-400/40">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-2">
                                    <p class="text-xs uppercase tracking-wide text-indigo-200">
                                        {{ $formatDateRange($event->startAt, $event->endAt) }}
                                    </p>
                                    <h3 class="text-lg font-semibold text-white">
                                        {{ $event->title }}
                                    </h3>
                                    <p class="text-xs text-gray-400">
                                        Oleh
                                        @if($event->organizer)
                                            <a href="{{ route('organizers.show', $event->organizer->id) }}" class="text-indigo-200 hover:text-indigo-100">
                                                {{ $event->organizer->displayName }}
                                            </a>
                                        @else
                                            <span class="text-indigo-200">-</span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-300 line-clamp-2">
                                        {{ $event->description ?? 'Belum ada deskripsi event.' }}
                                    </p>
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-gray-400">
                                        <span class="flex items-center gap-1">Lokasi {{ $event->location }}</span>
                                        @if ($event->category)
                                            <span class="rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2 py-0.5 text-indigo-200">
                                                {{ $event->category }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="rounded-full border border-indigo-500/30 bg-indigo-500/10 px-3 py-1 text-xs uppercase tracking-wide text-indigo-200">
                                    Tiket
                                </span>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-sm text-indigo-50">
                                <span>{{ $priceLabel($event) }}</span>
                                <a href="{{ route('events.show', $event->id) }}" class="rounded-lg border border-indigo-400 px-4 py-1 text-xs font-semibold text-indigo-100 hover:bg-indigo-500/20">
                                    Detail Event
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

<section class="space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-indigo-200">
                Agenda Terbaru
            </p>
            <h2 class="text-3xl font-semibold text-white md:text-4xl">
                Event mendatang yang wajib kamu pantau
            </h2>
            <p class="mt-2 max-w-2xl text-gray-400">
                Kami perbarui daftar ini setiap hari. Dapatkan inspirasi event, mulai dari konser intim hingga konferensi kreatif.
            </p>
        </div>
        <a href="{{ route('events.index') }}" class="rounded-lg border border-indigo-400 px-5 py-2 text-sm font-semibold text-indigo-200 hover:bg-indigo-500/20">
            Jelajahi Semua Event
        </a>
    </div>

    @if ($upcomingEvents->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-10 text-center text-gray-400">
            Belum ada event terbaru saat ini. Coba kembali beberapa saat lagi.
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($upcomingEvents as $event)
                <div class="flex h-full flex-col gap-4 rounded-2xl border border-gray-800 bg-gray-900/70 p-5 transition hover:border-indigo-400/40">
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span class="flex items-center gap-2">Tanggal {{ $formatDateRange($event->startAt, $event->endAt) }}</span>
                        <span class="flex items-center gap-2">Lokasi {{ $event->location }}</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">{{ $event->title }}</h3>
                    <p class="text-sm text-gray-400 line-clamp-3">
                        {{ $event->description ?? 'Belum ada deskripsi event.' }}
                    </p>
                    <div class="mt-auto flex items-center justify-between text-sm text-indigo-100">
                        <span>{{ $priceLabel($event) }}</span>
                        <a href="{{ route('events.show', $event->id) }}" class="rounded-lg bg-indigo-500 px-4 py-1 text-xs font-semibold text-white hover:bg-indigo-400">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="space-y-8">
    <div class="flex flex-col items-center text-center gap-3">
        <span class="rounded-full border border-pink-500/30 bg-pink-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-pink-200">
            Why Eventify
        </span>
        <h2 class="text-3xl font-semibold text-white md:text-4xl">
            Semua tools yang kamu butuhkan dalam satu platform
        </h2>
        <p class="max-w-3xl text-gray-400">
            Kami merancang dashboard dan pengalaman user-journey untuk mendukung organizer profesional maupun komunitas independen.
        </p>
    </div>
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($featureCards as $index => $card)
            <div class="h-full rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
                <div class="flex items-start gap-4">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full border border-indigo-500/30 bg-indigo-500/10 text-sm font-semibold text-indigo-200">
                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div>
                        <h3 class="text-lg font-semibold text-white">{{ $card['title'] }}</h3>
                        <p class="mt-2 text-sm text-gray-400">{{ $card['description'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="rounded-3xl border border-indigo-500/20 bg-indigo-500/10 p-10">
    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-xl space-y-4">
            <span class="inline-flex items-center rounded-full border border-indigo-400/30 bg-indigo-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-indigo-200">
                Untuk Organizer
            </span>
            <h2 class="text-3xl font-semibold text-white md:text-4xl">
                Workflow cepat dari ide sampai pintu masuk venue.
            </h2>
            <p class="text-gray-100/80">
                Tinggalkan spreadsheet dan korespondensi manual. Eventify menyediakan alur kerja terpadu untuk mengelola penjualan tiket, validasi peserta, hingga evaluasi pasca-event.
            </p>
        </div>
        <div class="grid gap-4 lg:max-w-xl">
            @foreach ($organizerSteps as $index => $step)
                <div class="rounded-2xl border border-indigo-500/20 bg-gray-900/70 p-5">
                    <p class="text-sm font-semibold text-indigo-300">Langkah 0{{ $index + 1 }}</p>
                    <h3 class="mt-2 text-lg font-semibold text-white">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-300">{{ $step['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="space-y-8">
    <div class="flex flex-col items-center gap-3 text-center">
        <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-4 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-200">
            Yang Mereka Katakan
        </span>
        <h2 class="text-3xl font-semibold text-white md:text-4xl">
            Komunitas kami terus bertumbuh
        </h2>
        <p class="max-w-2xl text-gray-400">
            Organizer dan peserta memberikan masukan untuk membuat platform ini semakin kuat.
        </p>
    </div>
    <div class="grid gap-6 md:grid-cols-2">
        @foreach ($testimonials as $testimonial)
            <div class="rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
                <p class="text-lg italic text-gray-200">“{{ $testimonial['quote'] }}”</p>
                <div class="mt-4 flex items-center justify-between text-sm text-gray-400">
                    <div>
                        <p class="font-semibold text-white">{{ $testimonial['name'] }}</p>
                        <p>{{ $testimonial['position'] }}</p>
                    </div>
                    <span class="flex items-center gap-2 text-indigo-200">
                        <span>Komunitas Eventify</span>
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<section class="rounded-3xl border border-indigo-500/30 bg-gradient-to-br from-indigo-600/30 via-indigo-500/20 to-purple-500/20 p-10 text-center">
    <h2 class="text-3xl font-semibold text-white md:text-4xl">
        Siap meluncurkan event dengan dukungan penuh Eventify?
    </h2>
    <p class="mt-3 text-base text-indigo-50/90">
        Aktifkan akun organizer, pasarkan eventmu ke puluhan ribu audiens, dan kelola transaksi secara aman.
    </p>
    <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
        <a href="{{ route('register') }}" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
            Mulai Buat Event
        </a>
        <a href="{{ route('events.index') }}" class="rounded-lg border border-indigo-200 px-5 py-2 text-sm font-semibold text-indigo-100 hover:bg-indigo-500/20">
            Lihat Agenda Event
        </a>
    </div>
</section>
@endsection
