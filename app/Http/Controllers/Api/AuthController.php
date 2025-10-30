<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
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
        )->with('success', 'Registrasi berhasil. Selamat datang di Eventify!');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

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
        )->with('success', 'Login berhasil.');
    }

    public function me(Request $request): RedirectResponse
    {
        $user = $request->user()->load([
            'organizer:id,userId,displayName,bio,ratingsAvg,ratingsCount',
        ]);

        return redirect()->route(
            $user->role === User::ROLE_ORGANIZER
                ? 'organizer.dashboard'
                : 'customer.dashboard'
        );
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['sometimes', 'string', 'min:6'],
        ]);

        if (isset($validated['password'])) {
            $validated['passwordHash'] = $validated['password'];
            unset($validated['password']);
        }

        $user->fill($validated);
        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateOrganizer(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== User::ROLE_ORGANIZER) {
            abort(403, 'Hanya organizer yang dapat memperbarui profil ini.');
        }

        $validated = $request->validate([
            'displayName' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
        ]);

        $profile = OrganizerProfile::firstOrCreate(
            ['userId' => $user->id],
            ['displayName' => $user->name, 'bio' => '']
        );

        $profile->update($validated);

        return back()->with('success', 'Profil organizer berhasil diperbarui.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah keluar.');
    }
}
