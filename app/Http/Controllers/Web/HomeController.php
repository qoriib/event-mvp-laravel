<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $events = Event::with('ticketTypes')
            ->orderBy('startAt')
            ->take(6)
            ->get();

        $highlighted = $events->take(3);
        $upcoming = $events->slice(3);

        $categoryHints = $events
            ->pluck('category')
            ->filter()
            ->unique()
            ->take(5)
            ->values();

        if ($categoryHints->isEmpty()) {
            $categoryHints = collect(['Music', 'Festival', 'Workshop', 'Conference', 'Culture']);
        }

        return view('welcome', [
            'highlightedEvents' => $highlighted,
            'upcomingEvents' => $upcoming,
            'categoryHints' => $categoryHints,
        ]);
    }
}
