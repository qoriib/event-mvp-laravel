<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrganizerDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_ORGANIZER) {
            abort(403, 'Hanya organizer yang dapat mengakses halaman ini.');
        }

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

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

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

    public function createEvent(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $organizer = OrganizerProfile::where('userId', $user->id)->firstOrFail();

        $validated = $request->validate([
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

        $ticketTypes = collect($validated['ticketTypes'] ?? [])
            ->map(fn ($ticket) => array_filter($ticket, fn ($value) => $value !== null && $value !== ''))
            ->filter(fn ($ticket) => ! empty($ticket['name']))
            ->values();

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
}
