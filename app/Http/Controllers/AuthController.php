<?php

namespace App\Http\Controllers;

use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->passwordHash)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(
            $user->role === User::ROLE_ORGANIZER
                ? route('organizer.dashboard')
                : route('customer.dashboard')
        );
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
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
        )->with('success', 'Akun berhasil dibuat.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
