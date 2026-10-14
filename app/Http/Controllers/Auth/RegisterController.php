<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Tampilkan form register.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Proses register. Role selalu 'kasir' — pindah role hanya via seeder/admin.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'role' => 'kasir',
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('kasir.home');
    }
}
