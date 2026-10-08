<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {

        $requestData = $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required',
                'remember' => 'nullable|boolean',
            ],
            [
                'email.required' => 'Please enter your email',
                'email.email' => 'Please enter a valid email address',
                'password.required' => 'Please enter a password',
            ]
        );

        if (Auth::attempt($request->only(['email', 'password']), $request->boolean('remeber'))) {
            // Authentication passed

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', false));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records!']);
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register()
    {
        return 'register';
    }
}
