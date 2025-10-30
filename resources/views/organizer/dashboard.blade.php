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
        <h1 class="text-3xl font-semibold text-white">Dashboard Organizer</h1>
        <p class="text-sm text-gray-400">
            Kelola profil organisasi, buat event baru, dan validasi transaksi peserta dari satu tempat.
        </p>
    </header>

    @if(!$organizer)
        <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-10 text-center text-gray-400">
            Profil organizer belum tersedia. Lengkapi data organisasi di bawah ini untuk memulai.
        </div>
    @endif

    <section class="grid gap-6 md:grid-cols-[1.1fr,0.9fr]" id="profile">
        <form action="{{ route('organizer.profile.update') }}" method="POST" class="space-y-4 rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
            @csrf
            <h2 class="text-xl font-semibold text-white">Profil Organisasi</h2>
            <div class="space-y-2">
                <label for="displayName" class="text-sm font-medium text-gray-200">Nama Organisasi</label>
                <input
                    type="text"
                    id="displayName"
                    name="displayName"
                    value="{{ old('displayName', optional($organizer)->displayName ?? $user->name) }}"
                    required
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
            </div>
            <div class="space-y-2">
                <label for="bio" class="text-sm font-medium text-gray-200">Deskripsi</label>
                <textarea
                    id="bio"
                    name="bio"
                    rows="4"
                    class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                >{{ old('bio', $organizer->bio ?? '') }}</textarea>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                Simpan Profil
            </button>
        </form>

        <div class="grid gap-4">
            <div class="rounded-2xl border border-gray-700 bg-gray-900/70 p-6">
                <p class="text-sm text-gray-400">Total Event</p>
                <p class="mt-2 text-3xl font-semibold text-white">{{ $events->total() }}</p>
            </div>
            <div class="rounded-2xl border border-gray-700 bg-gray-900/70 p-6">
                <p class="text-sm text-gray-400">Rating Rata-rata</p>
                <p class="mt-2 text-3xl font-semibold text-white">
                    {{ number_format(optional($organizer)->ratingsAvg ?? 0, 2) }}
                    <span class="text-base font-medium text-gray-400">({{ optional($organizer)->ratingsCount ?? 0 }} ulasan)</span>
                </p>
            </div>
        </div>
    </section>

    <section class="space-y-6">
        <h2 class="text-2xl font-semibold text-white">Buat Event Baru</h2>
        <form action="{{ route('organizer.events.store') }}" method="POST" class="space-y-4 rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-200" for="title">Judul Event</label>
                    <input type="text" id="title" name="title" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old('title') }}" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-200" for="category">Kategori</label>
                    <input type="text" id="category" name="category" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old('category') }}" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-200" for="location">Lokasi</label>
                    <input type="text" id="location" name="location" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old('location') }}" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-200" for="capacity">Kapasitas</label>
                    <input type="number" id="capacity" name="capacity" min="1" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old('capacity', 100) }}" />
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-200" for="startAt">Mulai</label>
                    <input type="datetime-local" id="startAt" name="startAt" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old('startAt') }}" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-200" for="endAt">Selesai</label>
                    <input type="datetime-local" id="endAt" name="endAt" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old('endAt') }}" />
                </div>
            </div>
            <div class="space-y-2">
                <label class="text-sm font-medium text-gray-200" for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="4" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>
            <div class="space-y-3">
                <span class="text-sm font-semibold text-white">Jenis Tiket</span>
                @for ($i = 0; $i < 3; $i++)
                    <div class="grid gap-3 rounded-2xl border border-gray-800 bg-gray-950/60 p-4 sm:grid-cols-3">
                        <input type="text" name="ticketTypes[{{ $i }}][name]" placeholder="Nama tiket" class="rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old("ticketTypes.$i.name") }}" />
                        <input type="number" min="0" name="ticketTypes[{{ $i }}][priceIDR]" placeholder="Harga" class="rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old("ticketTypes.$i.priceIDR") }}" />
                        <input type="number" min="1" name="ticketTypes[{{ $i }}][quota]" placeholder="Kuota (opsional)" class="rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ old("ticketTypes.$i.quota") }}" />
                    </div>
                @endfor
                <p class="text-xs text-gray-500">Tambahkan baris baru dengan mengisi salah satu input kosong, atau sesuaikan melalui API untuk detail lebih lanjut.</p>
            </div>
            <div class="flex items-center gap-3">
                <input type="hidden" name="isPaid" value="0" />
                <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                    <input type="checkbox" name="isPaid" value="1" class="rounded border-gray-700 bg-gray-950 text-indigo-500 focus:ring-indigo-500" {{ old('isPaid', true) ? 'checked' : '' }} /> Event berbayar
                </label>
                <span class="text-xs text-gray-500">Hilangkan centang untuk event gratis.</span>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                Simpan Event
            </button>
        </form>
    </section>

    @if($organizer)
        <section class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-white">Event Aktif</h2>
                <span class="text-sm text-gray-400">Menampilkan {{ $events->count() }} dari {{ $events->total() }} event</span>
            </div>

            @if($events->total() === 0)
                <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                    Belum ada event yang terdaftar.
                </div>
            @else
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($events as $event)
                        @php
                            $isOldForEvent = old('_event_id') == $event->id;
                            $eventTicketTypes = $event->ticketTypes->values();
                            $oldTicketTypes = $isOldForEvent ? collect(old('ticketTypes', []))->values() : collect();
                            $ticketRepeats = max($eventTicketTypes->count(), $oldTicketTypes->count(), 3);
                            $oldTicketTypesData = $oldTicketTypes->toArray();
                            $editTitle = $isOldForEvent ? old('title', $event->title) : $event->title;
                            $editCategory = $isOldForEvent ? old('category', $event->category) : $event->category;
                            $editLocation = $isOldForEvent ? old('location', $event->location) : $event->location;
                            $editCapacity = $isOldForEvent ? old('capacity', $event->capacity) : $event->capacity;
                            $editStartAt = $isOldForEvent ? old('startAt') : optional($event->startAt)->format('Y-m-d\TH:i');
                            $editEndAt = $isOldForEvent ? old('endAt') : optional($event->endAt)->format('Y-m-d\TH:i');
                            $editDescription = $isOldForEvent ? old('description', $event->description) : $event->description;
                            $editIsPaid = (int) ($isOldForEvent ? old('isPaid', $event->isPaid ? 1 : 0) : ($event->isPaid ? 1 : 0));
                        @endphp
                        <article class="flex h-full flex-col gap-3 rounded-2xl border border-gray-800 bg-gray-900/70 p-6">
                            <header class="space-y-1">
                                <span class="text-xs uppercase tracking-wide text-indigo-200">
                                    {{ \Illuminate\Support\Carbon::parse($event->startAt)->translatedFormat('d M Y') }}
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
                                <span>Tiket tersedia: {{ $event->ticketTypes->count() }}</span>
                                <span>Kuota kursi: {{ number_format($event->capacity) }}</span>
                                <span>Tersisa: {{ number_format($event->seatsAvailable) }}</span>
                            </div>
                            <footer class="mt-auto space-y-3 text-sm text-indigo-100">
                                <div class="flex items-center justify-between">
                                    <span>Review: {{ $event->reviews->count() }}</span>
                                    <a href="{{ route('events.show', $event->id) }}" class="rounded-lg border border-indigo-500/40 px-3 py-1 text-xs font-semibold text-indigo-100 hover:bg-indigo-500/20">
                                        Lihat Detail
                                    </a>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button
                                        type="button"
                                        class="rounded-lg border border-gray-700 px-3 py-1 text-xs font-semibold text-gray-200 transition hover:border-indigo-400 hover:text-white"
                                        data-modal-target="edit-event-{{ $event->id }}"
                                        data-modal-toggle="edit-event-{{ $event->id }}"
                                    >
                                        Edit Event
                                    </button>
                                    <form action="{{ route('organizer.events.destroy', $event->id) }}" method="POST" onsubmit="return confirm('Hapus event ini? Tindakan tidak dapat dibatalkan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-500/40 px-3 py-1 text-xs font-semibold text-red-200 transition hover:bg-red-500/10">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </footer>
                        </article>

                        <div id="edit-event-{{ $event->id }}" tabindex="-1" aria-hidden="true" class="fixed left-0 right-0 top-0 z-50 hidden h-full w-full overflow-y-auto overflow-x-hidden bg-black/70 p-4 md:inset-0">
                            <div class="relative mx-auto w-full max-w-4xl">
                                <div class="relative space-y-5 rounded-3xl border border-gray-800 bg-gray-950/95 p-6 shadow-xl">
                                    <button type="button" class="absolute right-4 top-4 text-gray-500 hover:text-white" data-modal-hide="edit-event-{{ $event->id }}">
                                        <span class="sr-only">Tutup</span>
                                        &times;
                                    </button>
                                    <header class="space-y-1 pr-8">
                                        <h3 class="text-xl font-semibold text-white">Edit Event</h3>
                                        <p class="text-sm text-gray-400">Perbarui informasi event sesuai kebutuhanmu.</p>
                                    </header>
                                    <form action="{{ route('organizer.events.update', $event->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="_event_id" value="{{ $event->id }}" />
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-200" for="title-{{ $event->id }}">Judul Event</label>
                                                <input type="text" id="title-{{ $event->id }}" name="title" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $editTitle }}" />
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-200" for="category-{{ $event->id }}">Kategori</label>
                                                <input type="text" id="category-{{ $event->id }}" name="category" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $editCategory }}" />
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-200" for="location-{{ $event->id }}">Lokasi</label>
                                                <input type="text" id="location-{{ $event->id }}" name="location" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $editLocation }}" />
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-200" for="capacity-{{ $event->id }}">Kapasitas</label>
                                                <input type="number" id="capacity-{{ $event->id }}" name="capacity" min="1" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $editCapacity }}" />
                                            </div>
                                        </div>
                                        <div class="grid gap-4 sm:grid-cols-2">
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-200" for="startAt-{{ $event->id }}">Mulai</label>
                                                <input type="datetime-local" id="startAt-{{ $event->id }}" name="startAt" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $editStartAt }}" />
                                            </div>
                                            <div class="space-y-2">
                                                <label class="text-sm font-medium text-gray-200" for="endAt-{{ $event->id }}">Selesai</label>
                                                <input type="datetime-local" id="endAt-{{ $event->id }}" name="endAt" required class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $editEndAt }}" />
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <label class="text-sm font-medium text-gray-200" for="description-{{ $event->id }}">Deskripsi</label>
                                            <textarea id="description-{{ $event->id }}" name="description" rows="4" class="w-full rounded-lg border border-gray-700 bg-gray-950/60 px-4 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ $editDescription }}</textarea>
                                        </div>
                                        <div class="space-y-3">
                                            <span class="text-sm font-semibold text-white">Jenis Tiket</span>
                                            @for ($i = 0; $i < $ticketRepeats; $i++)
                                                @php
                                                    $ticket = $eventTicketTypes[$i] ?? null;
                                                    $nameValue = $isOldForEvent ? data_get($oldTicketTypesData, $i.'.name', $ticket->name ?? '') : ($ticket->name ?? '');
                                                    $priceValue = $isOldForEvent ? data_get($oldTicketTypesData, $i.'.priceIDR', $ticket->priceIDR ?? '') : ($ticket->priceIDR ?? '');
                                                    $quotaValue = $isOldForEvent ? data_get($oldTicketTypesData, $i.'.quota', $ticket->quota ?? '') : ($ticket->quota ?? '');
                                                @endphp
                                                <div class="grid gap-3 rounded-2xl border border-gray-800 bg-gray-900/50 p-4 sm:grid-cols-3">
                                                    <input type="text" name="ticketTypes[{{ $i }}][name]" placeholder="Nama tiket" class="rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $nameValue }}" />
                                                    <input type="number" min="0" name="ticketTypes[{{ $i }}][priceIDR]" placeholder="Harga" class="rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $priceValue }}" />
                                                    <input type="number" min="1" name="ticketTypes[{{ $i }}][quota]" placeholder="Kuota (opsional)" class="rounded-lg border border-gray-700 bg-gray-950/60 px-3 py-2 text-sm text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" value="{{ $quotaValue }}" />
                                                </div>
                                            @endfor
                                            <p class="text-xs text-gray-500">Kosongkan baris yang tidak diperlukan. Minimal satu tiket wajib diisi.</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <input type="hidden" name="isPaid" value="0" />
                                            <label class="inline-flex items-center gap-2 text-sm text-gray-300">
                                                <input type="checkbox" name="isPaid" value="1" class="rounded border-gray-700 bg-gray-950 text-indigo-500 focus:ring-indigo-500" {{ $editIsPaid ? 'checked' : '' }} /> Event berbayar
                                            </label>
                                        </div>
                                        <div class="flex items-center justify-end gap-3">
                                            <button type="button" class="rounded-lg border border-gray-700 px-4 py-2 text-sm font-semibold text-gray-200 hover:border-gray-500" data-modal-hide="edit-event-{{ $event->id }}">
                                                Batal
                                            </button>
                                            <button type="submit" class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-400">
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="pt-4">{{ $events->links('pagination::tailwind') }}</div>
            @endif
        </section>

        <section class="space-y-4">
            <h2 class="text-2xl font-semibold text-white">Transaksi Pesanan</h2>
            @if($managedTransactions->isEmpty())
                <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900/40 p-8 text-center text-gray-400">
                    Belum ada transaksi yang perlu ditinjau.
                </div>
            @else
                <div class="overflow-x-auto rounded-2xl border border-gray-800 bg-gray-900/70">
                    <table class="min-w-full divide-y divide-gray-800 text-sm text-gray-200">
                        <thead class="bg-gray-900/80 text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Peserta</th>
                                <th class="px-4 py-3 text-left">Event</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($managedTransactions as $transaction)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium text-white">{{ $transaction->user->name ?? 'Peserta' }}</p>
                                            <p class="text-xs text-gray-400">{{ $transaction->user->email ?? '-' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-300">
                                        {{ $transaction->event->title ?? '-' }}<br />
                                        {{ $transaction->items->pluck('ticketType.name')->implode(', ') ?: '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-gray-800/60 px-2 py-1 text-xs uppercase tracking-wide text-indigo-200">
                                            {{ $statusLabels[$transaction->status] ?? $transaction->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('transactions.status', $transaction->id) }}" method="POST" class="flex items-center gap-2 text-xs">
                                            @csrf
                                            <select name="status" class="rounded-lg border border-gray-700 bg-gray-950/60 px-2 py-1 text-gray-100 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                @foreach($statusLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected($transaction->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-lg bg-indigo-500 px-3 py-1 font-semibold text-white hover:bg-indigo-400">
                                                Update
                                            </button>
                                        </form>
                                        @if($transaction->paymentProofUrl)
                                            <a href="{{ $transaction->paymentProofUrl }}" target="_blank" class="mt-2 block text-xs text-indigo-300 hover:text-indigo-100">Lihat bukti pembayaran</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="pt-4">{{ $managedTransactions->links('pagination::tailwind') }}</div>
            @endif
        </section>
    @endif
</section>
@endsection
