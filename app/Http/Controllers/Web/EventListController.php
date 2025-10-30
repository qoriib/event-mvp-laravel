<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventListController extends Controller
{
    public function __invoke(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $location = trim((string) $request->query('location', ''));

        $eventsQuery = Event::with(['ticketTypes', 'organizer'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($builder) use ($search) {
                    $builder->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($location, fn ($query) => $query->where('location', 'like', "%{$location}%"));

        $events = $eventsQuery
            ->orderBy('startAt')
            ->paginate(9)
            ->withQueryString();

        $categoryOptions = Event::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        $locationOptions = Event::query()
            ->whereNotNull('location')
            ->distinct()
            ->orderBy('location')
            ->pluck('location')
            ->filter()
            ->values();

        return view('events.index', [
            'events' => $events,
            'categoryOptions' => $categoryOptions,
            'locationOptions' => $locationOptions,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'location' => $location,
            ],
        ]);
    }
}
