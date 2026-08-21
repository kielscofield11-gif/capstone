<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\PasswordPolicy;

class RegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('throttle:3,1')->only('register');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => ['required', 'string', 'confirmed', PasswordPolicy::rule()],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'staff',
            'is_active' => false,
        ]);

        return redirect()->route('login')
            ->with('success', 'Registration submitted. Your account is awaiting admin approval before you can sign in.');
    }
}
