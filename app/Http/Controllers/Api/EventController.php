<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('events.index', $request->query());
    }

    public function mine(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== $user::ROLE_ORGANIZER) {
            abort(403);
        }

        $organizer = OrganizerProfile::where('userId', $user->id)->first();

        if (! $organizer) {
            return redirect()->route('organizer.dashboard')->with('error', 'Profil organizer belum tersedia.');
        }

        return redirect()->route('organizer.dashboard');
    }

    public function show(Event $event): RedirectResponse
    {
        return redirect()->route('events.show', $event);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== $user::ROLE_ORGANIZER) {
            abort(403);
        }

        $organizer = OrganizerProfile::where('userId', $user->id)->first();
        if (! $organizer) {
            return back()->with('error', 'Profil organizer belum tersedia.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'startAt' => ['required', 'date'],
            'endAt' => ['required', 'date', 'after_or_equal:startAt'],
            'isPaid' => ['required', 'boolean'],
            'capacity' => ['required', 'integer', 'min:1'],
            'ticketTypes' => ['required', 'array', 'min:1'],
            'ticketTypes.*.name' => ['required', 'string', 'max:120'],
            'ticketTypes.*.priceIDR' => ['required', 'integer', 'min:0'],
            'ticketTypes.*.quota' => ['nullable', 'integer', 'min:1'],
        ]);

        $ticketTypes = collect($validated['ticketTypes'])
            ->map(fn ($ticket) => array_filter($ticket, fn ($value) => $value !== null && $value !== ''))
            ->filter(fn ($ticket) => ! empty($ticket['name']))
            ->values();

        if ($ticketTypes->isEmpty()) {
            return back()->with('error', 'Minimal satu tiket harus diisi.');
        }

        $event = DB::transaction(function () use ($validated, $organizer, $ticketTypes) {
            $event = Event::create([
                'organizerId' => $organizer->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'location' => $validated['location'],
                'startAt' => Carbon::parse($validated['startAt']),
                'endAt' => Carbon::parse($validated['endAt']),
                'isPaid' => $validated['isPaid'],
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

            return $event->load('ticketTypes');
        });

        return redirect()->route('events.show', $event)->with('success', 'Event baru berhasil dibuat.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== $user::ROLE_ORGANIZER) {
            abort(403);
        }

        $organizer = OrganizerProfile::where('userId', $user->id)->first();
        if (! $organizer || $event->organizerId !== $organizer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'startAt' => ['nullable', 'date'],
            'endAt' => ['nullable', 'date', 'after_or_equal:startAt'],
            'isPaid' => ['required', 'boolean'],
            'capacity' => ['required', 'integer', 'min:1'],
            'ticketTypes' => ['nullable', 'array'],
            'ticketTypes.*.name' => ['required_with:ticketTypes', 'string', 'max:120'],
            'ticketTypes.*.priceIDR' => ['required_with:ticketTypes', 'integer', 'min:0'],
            'ticketTypes.*.quota' => ['nullable', 'integer', 'min:1'],
        ]);

        $event = DB::transaction(function () use ($event, $validated) {
            $event->fill([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'category' => $validated['category'] ?? null,
                'location' => $validated['location'],
                'isPaid' => $validated['isPaid'],
            ]);

            if (isset($validated['startAt'])) {
                $event->startAt = Carbon::parse($validated['startAt']);
            }

            if (isset($validated['endAt'])) {
                $event->endAt = Carbon::parse($validated['endAt']);
            }

            if (isset($validated['capacity'])) {
                $event->seatsAvailable = max($validated['capacity'], $event->seatsAvailable);
                $event->capacity = $validated['capacity'];
            }

            $event->save();

            if (isset($validated['ticketTypes'])) {
                $event->ticketTypes()->delete();
                $event->ticketTypes()->createMany(
                    collect($validated['ticketTypes'])->map(fn ($ticket) => [
                        'name' => $ticket['name'],
                        'priceIDR' => $ticket['priceIDR'],
                        'quota' => $ticket['quota'] ?? null,
                    ])->all()
                );
            }

            return $event->load('ticketTypes');
        });

        return redirect()->route('events.show', $event)->with('success', 'Event berhasil diperbarui.');
    }

    public function organizerDetail(OrganizerProfile $organizer): RedirectResponse
    {
        return redirect()->route('organizer.dashboard');
    }
}
