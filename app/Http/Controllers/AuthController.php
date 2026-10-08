<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->only(['email', 'password']), $request->boolean('remeber'))) {
            // Authentication passed
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard', false));
        }


        return back()->withInput()->withErrors(['email' => 'These credentials do not match our records!']);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated('password')),
        ]);

        Auth::login($user);

        return 'register';
    }
}