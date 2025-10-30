<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EventContentController extends Controller
{
    public function home(): View
    {
        $events = Event::with(['ticketTypes', 'organizer'])
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

    public function list(Request $request): View
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

    public function show(Request $request, Event $event): View
    {
        $event->load([
            'organizer.user:id,name,email',
            'ticketTypes',
            'reviews.user:id,name',
        ]);

        /** @var User|null $user */
        $user = $request->user();

        $customerTransactions = collect();
        $managedTransactions = collect();
        $isOrganizerOwner = false;

        $userReview = null;
        $canReview = false;

        if ($user) {
            if ($user->role === User::ROLE_CUSTOMER) {
                $customerTransactions = Transaction::with(['items.ticketType'])
                    ->where('userId', $user->id)
                    ->where('eventId', $event->id)
                    ->orderByDesc('created_at')
                    ->get();

                $userReview = Review::where('eventId', $event->id)
                    ->where('userId', $user->id)
                    ->first();

                $canReview = $customerTransactions->contains(
                    fn ($transaction) => $transaction->status === Transaction::STATUS_DONE
                ) && ! $userReview;
            } elseif ($user->role === User::ROLE_ORGANIZER) {
                $organizer = OrganizerProfile::where('userId', $user->id)->first();
                if ($organizer && $organizer->id === $event->organizerId) {
                    $isOrganizerOwner = true;
                    $managedTransactions = Transaction::with([
                        'user:id,name,email',
                        'items.ticketType:id,name',
                    ])
                        ->where('eventId', $event->id)
                        ->latest()
                        ->get();
                }
            }
        }

        $statusOptions = [
            Transaction::STATUS_WAITING_PAYMENT,
            Transaction::STATUS_WAITING_CONFIRMATION,
            Transaction::STATUS_DONE,
            Transaction::STATUS_REJECTED,
            Transaction::STATUS_EXPIRED,
            Transaction::STATUS_CANCELED,
        ];

        return view('events.show', [
            'event' => $event,
            'user' => $user,
            'customerTransactions' => $customerTransactions,
            'managedTransactions' => $managedTransactions,
            'statusOptions' => $statusOptions,
            'isOrganizerOwner' => $isOrganizerOwner,
            'userReview' => $userReview,
            'canReview' => $canReview,
        ]);
    }
}
