<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:72'],
            'role' => ['required', 'in:klien,developer'],
        ], [
            'nama.required' => 'Nama wajib diisi (maksimal 100 karakter).',
            'email.required' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.max' => 'Password maksimal 72 karakter.',
            'role.in' => 'Peran tidak valid.',
        ]);

        $user = User::create([
            'nama' => $validated['nama'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('login')->with('flash', ['Pendaftaran berhasil. Silakan masuk.', 'ok']);
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['email' => strtolower($credentials['email']), 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            return $this->redirectUser(Auth::user())->with('flash', ['Selamat datang kembali.', 'ok']);
        }

        return back()->withInput()->with('flash', ['Email atau password salah.', 'err']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('flash', ['Anda telah keluar.', 'ok']);
    }

    protected function redirectUser(User $user)
    {
        if ($user->isAdmin()) {
            return redirect()->route('admin.index');
        }
        return redirect()->route('dashboard');
    }
}
