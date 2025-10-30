<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_CUSTOMER) {
            abort(403, 'Hanya pelanggan yang dapat mengakses halaman ini.');
        }

        $transactions = Transaction::with([
            'event:id,title,location,startAt,endAt',
            'items.ticketType:id,name',
        ])
            ->where('userId', $user->id)
            ->latest()
            ->paginate(8);

        return view('customer.dashboard', [
            'user' => $user,
            'transactions' => $transactions,
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_if($user->role !== User::ROLE_CUSTOMER, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        if (! empty($validated['password'])) {
            $user->passwordHash = $validated['password'];
        }

        unset($validated['password']);

        $user->fill($validated);
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
