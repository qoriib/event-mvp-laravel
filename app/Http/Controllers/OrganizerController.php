<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganizerController extends Controller
{
    public function dashboard(Request $request): View
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_if(! $user || $user->role !== User::ROLE_ORGANIZER, 403, 'Hanya organizer yang dapat mengakses halaman ini.');

        $organizer = OrganizerProfile::with([
            'events.ticketTypes',
            'events.reviews.user:id,name',
        ])->where('userId', $user->id)->first();

        $events = $organizer
            ? $organizer->events()->with('ticketTypes')->latest()->paginate(6)
            : new LengthAwarePaginator([], 0, 6, 1, ['path' => $request->url(), 'query' => $request->query()]);

        $managedTransactions = $organizer
            ? Transaction::with([
                'user:id,name,email',
                'event:id,title,organizerId,startAt,endAt,location',
                'items.ticketType:id,name',
            ])
                ->whereHas('event', fn ($query) => $query->where('organizerId', optional($organizer)->id))
                ->latest()
                ->paginate(10)
            : new LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url(), 'query' => $request->query()]);

        return view('organizer.dashboard', [
            'user' => $user,
            'organizer' => $organizer,
            'events' => $events,
            'managedTransactions' => $managedTransactions,
        ]);
    }

    public function showPublic(OrganizerProfile $organizer): View
    {
        $organizer->load(['user:id,name,email']);

        $events = $organizer->events()->with('ticketTypes')->latest()->paginate(6);

        $reviewsQuery = Review::with(['user:id,name', 'event:id,title'])
            ->whereHas('event', fn ($query) => $query->where('organizerId', $organizer->id))
            ->latest();

        $reviews = $reviewsQuery->paginate(8, ['*'], 'reviews_page')->withQueryString();

        return view('organizer.profile', [
            'organizer' => $organizer,
            'events' => $events,
            'reviews' => $reviews,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_if(! $user || $user->role !== User::ROLE_ORGANIZER, 403);

        $validated = $request->validate([
            'displayName' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
        ]);

        $profile = OrganizerProfile::firstOrCreate(['userId' => $user->id], [
            'displayName' => $user->name,
            'bio' => '',
        ]);

        $profile->update($validated);

        return back()->with('success', 'Profil organizer berhasil diperbarui.');
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $organizer = $this->resolveOrganizer($request);

        $validated = $this->validateEventPayload($request);

        $ticketTypes = $this->extractTicketTypes($validated['ticketTypes'] ?? []);

        if ($ticketTypes->isEmpty()) {
            return back()->with('error', 'Minimal satu tiket harus diisi.')->withInput();
        }

        DB::transaction(function () use ($organizer, $validated, $ticketTypes) {
            /** @var Event $event */
            $event = Event::create([
                'organizerId' => $organizer->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'location' => $validated['location'],
                'startAt' => Carbon::parse($validated['startAt']),
                'endAt' => Carbon::parse($validated['endAt']),
                'isPaid' => (bool) ($validated['isPaid'] ?? false),
                'capacity' => $validated['capacity'],
                'seatsAvailable' => $validated['capacity'],
            ]);

            $event->ticketTypes()->createMany(
                $ticketTypes->map(fn ($ticket) => [
                    'name' => $ticket['name'],
                    'priceIDR' => $ticket['priceIDR'],
                    'quota' => $ticket['quota'] ?? null,
                ])->all()
            );
        });

        return back()->with('success', 'Event baru berhasil dibuat.');
    }

    public function updateEvent(Request $request, Event $event): RedirectResponse
    {
        $organizer = $this->resolveOrganizer($request);

        abort_if($event->organizerId !== $organizer->id, 403, 'Event ini bukan milik kamu.');

        $validated = $this->validateEventPayload($request);
        $ticketTypes = $this->extractTicketTypes($validated['ticketTypes'] ?? []);

        if ($ticketTypes->isEmpty()) {
            return back()->with('error', 'Minimal satu tiket harus diisi.')->withInput();
        }

        DB::transaction(function () use ($event, $validated, $ticketTypes) {
            $newCapacity = $validated['capacity'];
            $capacityDiff = $newCapacity - $event->capacity;
            $newSeatsAvailable = max(0, min($newCapacity, $event->seatsAvailable + $capacityDiff));

            $event->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'location' => $validated['location'],
                'startAt' => Carbon::parse($validated['startAt']),
                'endAt' => Carbon::parse($validated['endAt']),
                'isPaid' => (bool) ($validated['isPaid'] ?? false),
                'capacity' => $newCapacity,
                'seatsAvailable' => $newSeatsAvailable,
            ]);

            $event->ticketTypes()->delete();
            $event->ticketTypes()->createMany(
                $ticketTypes->map(fn ($ticket) => [
                    'name' => $ticket['name'],
                    'priceIDR' => $ticket['priceIDR'],
                    'quota' => $ticket['quota'] ?? null,
                ])->all()
            );
        });

        return back()->with('success', 'Event berhasil diperbarui.');
    }

    public function destroyEvent(Request $request, Event $event): RedirectResponse
    {
        $organizer = $this->resolveOrganizer($request);

        abort_if($event->organizerId !== $organizer->id, 403, 'Event ini bukan milik kamu.');

        if ($event->transactions()->exists()) {
            return back()->with('error', 'Event tidak dapat dihapus karena memiliki transaksi aktif.');
        }

        DB::transaction(function () use ($event) {
            $event->ticketTypes()->delete();
            $event->delete();
        });

        return back()->with('success', 'Event berhasil dihapus.');
    }

    private function resolveOrganizer(Request $request): OrganizerProfile
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_if(! $user || $user->role !== User::ROLE_ORGANIZER, 403, 'Hanya organizer yang dapat melakukan aksi ini.');

        return OrganizerProfile::where('userId', $user->id)->firstOrFail();
    }

    private function validateEventPayload(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'startAt' => ['required', 'date'],
            'endAt' => ['required', 'date', 'after_or_equal:startAt'],
            'isPaid' => ['nullable', 'boolean'],
            'capacity' => ['required', 'integer', 'min:1'],
            'ticketTypes' => ['required', 'array', 'min:1'],
            'ticketTypes.*.name' => ['required', 'string', 'max:120'],
            'ticketTypes.*.priceIDR' => ['required', 'integer', 'min:0'],
            'ticketTypes.*.quota' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    private function extractTicketTypes(array $ticketTypes)
    {
        return collect($ticketTypes)
            ->map(fn ($ticket) => array_filter($ticket, fn ($value) => $value !== null && $value !== ''))
            ->filter(fn ($ticket) => ! empty($ticket['name']))
            ->values();
    }
}
