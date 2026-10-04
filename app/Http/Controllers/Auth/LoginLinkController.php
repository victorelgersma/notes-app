<?php

namespace App\Http\Controllers\Auth;

use App\Actions\SendLoginLink;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginLinkController extends Controller
{
    public function store(Request $request, SendLoginLink $sendLoginLink): RedirectResponse
    {
        // Honeypot — a hidden field real users never fill in.
        if ($request->filled('website')) {
            return redirect()->route('login')->with('status', 'login-link-sent');
        }

        $email = Str::lower($request->validate(['email' => ['required', 'email', 'max:255']])['email']);

        $throttleKey = $email.'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            throw ValidationException::withMessages([
                'email' => __('Please wait a moment before requesting another link.'),
            ]);
        }
        RateLimiter::hit($throttleKey, 60);

        $user = User::where('email', $email)->first();

        if ($user === null) {
            try {
                $user = User::create(['email' => $email]);
            } catch (UniqueConstraintViolationException) {
                // Lost the race with a concurrent request for the same
                // email — fetch what it created instead of failing.
                $user = User::where('email', $email)->first();
            }
        }

        $sendLoginLink($user);

        return redirect()->route('login')->with('status', 'login-link-sent');
    }

    public function authenticate(Request $request, User $user): RedirectResponse
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('notes.index', absolute: false));
    }
}

