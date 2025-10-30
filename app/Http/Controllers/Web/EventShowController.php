<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventShowController extends Controller
{
    public function __invoke(Request $request, Event $event): View
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
