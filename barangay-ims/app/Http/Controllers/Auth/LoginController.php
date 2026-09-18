<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:5,1')->only('login');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt(array_merge($credentials, ['is_active' => true]), $request->boolean('remember'))) {
            $request->session()->regenerate();

            try {
                app(AuditLogger::class)->event('login', $request->user(), 'Signed in');
            } catch (\Throwable) {
                // Audit must never block authentication.
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records or your account is inactive.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            try {
                app(AuditLogger::class)->event('logout', $user, 'Signed out');
            } catch (\Throwable) {
                // Audit must never block logout.
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

}
