<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TransactionsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Ticket::truncate();
        TransactionItem::truncate();
        Transaction::truncate();
        Schema::enableForeignKeyConstraints();

        $users = User::where('role', User::ROLE_CUSTOMER)->get();
        $ticketTypes = TicketType::with('event')->get();
        $statusCycle = [
            Transaction::STATUS_DONE,
            Transaction::STATUS_WAITING_PAYMENT,
            Transaction::STATUS_WAITING_CONFIRMATION,
            Transaction::STATUS_REJECTED,
        ];

        if ($users->isEmpty() || $ticketTypes->isEmpty()) {
            return;
        }

        foreach (range(0, 14) as $index) {
            $user = $users[$index % $users->count()];
            $ticketType = $ticketTypes->random();
            $price = $ticketType->priceIDR;
            $qty = 1 + ($index % 3);
            $subtotal = $price * $qty;
            $usesPromo = $index % 4 === 0;
            $promoDiscount = $usesPromo ? (int) round($subtotal * 0.1) : 0;
            $pointsUsed = random_int(0, 5000);

            $transaction = Transaction::create([
                'userId' => $user->id,
                'eventId' => $ticketType->eventId,
                'status' => $statusCycle[$index % count($statusCycle)],
                'totalBeforeIDR' => $subtotal,
                'pointsUsedIDR' => $pointsUsed,
                'promoCode' => $usesPromo ? 'PROMO1' : null,
                'promoDiscountIDR' => $promoDiscount,
                'totalPayableIDR' => max($subtotal - $promoDiscount - $pointsUsed, 0),
                'paymentProofUrl' => null,
                'paymentProofAt' => null,
                'expiresAt' => now()->addHours(2),
                'decisionDueAt' => now()->addDays(3),
            ]);

            TransactionItem::create([
                'transactionId' => $transaction->id,
                'ticketTypeId' => $ticketType->id,
                'qty' => $qty,
                'unitPriceIDR' => $price,
                'lineTotalIDR' => $subtotal,
            ]);

            if ($index % 3 === 0) {
                Ticket::create([
                    'eventId' => $ticketType->eventId,
                    'transactionId' => $transaction->id,
                    'ticketTypeId' => $ticketType->id,
                    'ownerUserId' => $user->id,
                    'checkedInAt' => null,
                ]);
            }
        }
    }
}
