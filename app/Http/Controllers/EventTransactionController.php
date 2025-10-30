<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Review;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventTransactionController extends Controller
{
    public function purchase(Request $request, Event $event): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_CUSTOMER) {
            return redirect()->route('login')->with('error', 'Silakan masuk sebagai customer untuk membeli tiket.');
        }

        $validated = $request->validate([
            'ticketTypeId' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        /** @var TicketType|null $ticketType */
        $ticketType = $event->ticketTypes()->find($validated['ticketTypeId']);
        if (! $ticketType) {
            return back()->with('error', 'Jenis tiket tidak valid.');
        }

        $qty = $validated['qty'];
        $unitPrice = (int) $ticketType->priceIDR;
        $subtotal = $unitPrice * $qty;
        $status = Transaction::STATUS_WAITING_PAYMENT;

        DB::transaction(function () use ($user, $event, $ticketType, $qty, $unitPrice, $subtotal, $status) {
            /** @var Transaction $transaction */
            $transaction = Transaction::create([
                'userId' => $user->id,
                'eventId' => $event->id,
                'status' => $status,
                'totalBeforeIDR' => $subtotal,
                'pointsUsedIDR' => 0,
                'promoCode' => null,
                'promoDiscountIDR' => 0,
                'totalPayableIDR' => $subtotal,
                'expiresAt' => $status === Transaction::STATUS_WAITING_PAYMENT
                    ? Carbon::now()->addHours(2)
                    : Carbon::now(),
                'decisionDueAt' => $status === Transaction::STATUS_WAITING_CONFIRMATION
                    ? Carbon::now()->addDays(3)
                    : null,
            ]);

            TransactionItem::create([
                'transactionId' => $transaction->id,
                'ticketTypeId' => $ticketType->id,
                'qty' => $qty,
                'unitPriceIDR' => $unitPrice,
                'lineTotalIDR' => $subtotal,
            ]);
        });

        return back()->with('success', 'Transaksi dibuat. Silakan unggah bukti pembayaran sebelum 2 jam.');
    }

    public function uploadProof(Request $request, Transaction $transaction): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $transaction->userId !== $user->id) {
            return back()->with('error', 'Kamu tidak diizinkan mengunggah bukti pembayaran ini.');
        }

        if ($transaction->status !== Transaction::STATUS_WAITING_PAYMENT) {
            return back()->with('error', 'Transaksi ini tidak memerlukan bukti pembayaran.');
        }

        $request->validate([
            'paymentProof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ]);

        $path = $request->file('paymentProof')->store('payment-proofs', 'public');

        $transaction->update([
            'paymentProofUrl' => Storage::disk('public')->url($path),
            'paymentProofAt' => now(),
            'status' => Transaction::STATUS_WAITING_CONFIRMATION,
            'decisionDueAt' => now()->addDays(3),
        ]);

        return back()->with('success', 'Bukti pembayaran telah diunggah. Penyelenggara akan memverifikasi dalam 3 hari.');
    }

    public function updateStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_ORGANIZER) {
            return back()->with('error', 'Hanya organizer yang dapat memperbarui status transaksi.');
        }

        $organizer = OrganizerProfile::where('userId', $user->id)->first();
        if (! $organizer || $transaction->event?->organizerId !== $organizer->id) {
            return back()->with('error', 'Transaksi ini bukan milik event kamu.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Transaction::STATUS_WAITING_PAYMENT,
                Transaction::STATUS_WAITING_CONFIRMATION,
                Transaction::STATUS_DONE,
                Transaction::STATUS_REJECTED,
                Transaction::STATUS_EXPIRED,
                Transaction::STATUS_CANCELED,
            ])],
        ]);

        $previousStatus = $transaction->status;

        DB::transaction(function () use ($transaction, $validated, $previousStatus) {
            $transaction->update(['status' => $validated['status']]);

            if ($validated['status'] === Transaction::STATUS_DONE && $previousStatus !== Transaction::STATUS_DONE) {
                $transaction->loadMissing('items');

                foreach ($transaction->items as $item) {
                    for ($i = 0; $i < $item->qty; $i++) {
                        Ticket::create([
                            'eventId' => $transaction->eventId,
                            'transactionId' => $transaction->id,
                            'ticketTypeId' => $item->ticketTypeId,
                            'ownerUserId' => $transaction->userId,
                        ]);
                    }
                }
            }

            if (in_array($validated['status'], [
                Transaction::STATUS_CANCELED,
                Transaction::STATUS_REJECTED,
                Transaction::STATUS_EXPIRED,
            ], true) && $transaction->pointsUsedIDR > 0) {
                $transaction->user()->increment('pointsBalance', $transaction->pointsUsedIDR);
            }
        });

        return back()->with('success', 'Status transaksi berhasil diperbarui.');
    }

    public function storeReview(Request $request, Event $event): RedirectResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_CUSTOMER) {
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
