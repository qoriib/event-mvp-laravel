<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizerProfileController extends Controller
{
    public function __invoke(Request $request, OrganizerProfile $organizer): View
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
}
