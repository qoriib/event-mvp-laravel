<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_CUSTOMER, User::ROLE_ORGANIZER])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'passwordHash' => $validated['password'],
            'role' => $validated['role'],
        ]);

        if ($user->role === User::ROLE_ORGANIZER) {
            OrganizerProfile::create([
                'userId' => $user->id,
                'displayName' => $user->name,
                'bio' => '',
                'ratingsAvg' => 0,
                'ratingsCount' => 0,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route(
            $user->role === User::ROLE_ORGANIZER
                ? 'organizer.dashboard'
                : 'customer.dashboard'
        );
    }
}
