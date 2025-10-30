<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrganizerProfile;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('customer.dashboard');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('events.show', $request->input('eventId'))
            ->with('info', 'Gunakan halaman detail event untuk melakukan pembelian.');
    }

    public function uploadProof(Request $request, Transaction $transaction): RedirectResponse
    {
        return redirect()->route('events.show', $transaction->eventId);
    }

    public function updateStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        return redirect()->route('events.show', $transaction->eventId);
    }

    public function manage(Request $request): RedirectResponse
    {
        return redirect()->route('organizer.dashboard');
    }
}
