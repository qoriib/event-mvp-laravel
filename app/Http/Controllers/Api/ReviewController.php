<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('events.show', $request->query('eventId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'eventId' => ['required', 'integer', 'exists:events,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string'],
        ]);

        $hasCompletedTransaction = Transaction::where('userId', $user->id)
            ->where('eventId', $validated['eventId'])
            ->where('status', Transaction::STATUS_DONE)
            ->exists();

        if (! $hasCompletedTransaction) {
            return back()->with('error', 'Hanya peserta yang telah menghadiri event yang dapat memberikan review.');
        }

        $existing = Review::where('eventId', $validated['eventId'])
            ->where('userId', $user->id)
            ->exists();

        if ($existing) {
            return back()->with('error', 'Kamu sudah memberikan review untuk event ini.');
        }

        $review = Review::create([
            'eventId' => $validated['eventId'],
            'userId' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        /** @var Event $event */
        $event = Event::with('organizer')->find($validated['eventId']);

        if ($event && $event->organizer) {
            $aggregate = Review::whereHas('event', fn ($query) => $query->where('organizerId', $event->organizerId))
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
                ->first();

            OrganizerProfile::where('id', $event->organizerId)->update([
                'ratingsAvg' => $aggregate?->avg_rating ?? 0,
                'ratingsCount' => $aggregate?->total_reviews ?? 0,
            ]);
        }

        return back()->with('success', 'Terima kasih! Review kamu telah disimpan.');
    }
}
