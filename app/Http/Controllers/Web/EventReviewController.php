<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventReviewController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user || $user->role !== \App\Models\User::ROLE_CUSTOMER) {
            return redirect()->route('login')->with('error', 'Masuk sebagai customer untuk menulis ulasan.');
        }

        $hasCompletedTransaction = Transaction::query()
            ->where('eventId', $event->id)
            ->where('userId', $user->id)
            ->where('status', Transaction::STATUS_DONE)
            ->exists();

        if (! $hasCompletedTransaction) {
            return back()->with('error', 'Kamu perlu menyelesaikan transaksi sebelum memberikan ulasan.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($event, $user, $validated) {
            Review::updateOrCreate(
                ['eventId' => $event->id, 'userId' => $user->id],
                [
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'] ?? null,
                ]
            );

            $organizer = OrganizerProfile::find($event->organizerId);

            if ($organizer) {
                $aggregate = Review::query()
                    ->whereHas('event', fn ($query) => $query->where('organizerId', $organizer->id))
                    ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
                    ->first();

                $organizer->update([
                    'ratingsAvg' => (float) ($aggregate->avg_rating ?? 0),
                    'ratingsCount' => (int) ($aggregate->total_reviews ?? 0),
                ]);
            }
        });

        return back()->with('success', 'Terima kasih! Ulasan kamu telah disimpan.');
    }
}
