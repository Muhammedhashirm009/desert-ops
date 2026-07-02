<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the unified login page.
     */
    public function showLogin(Request $request)
    {
        // If already logged in as Admin, go to dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // If already logged into an outlet session, go to outlet dashboard
        if (Auth::guard('outlet')->check()) {
            return redirect()->route('portal.dashboard');
        }

        // If already logged into an accountant session, go to accounting dashboard
        if (Auth::guard('accountant')->check()) {
            return redirect()->route('accounting.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle Unified Authentication.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // 1. Try to login as Admin (standard user using web guard)
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Logged in to Central Kitchen ERP.');
        }

        // 2. Try to login as Accountant (using accountant guard)
        if (Auth::guard('accountant')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $accountant = Auth::guard('accountant')->user();
            session(['portal_accountant_id' => $accountant->id]);

            return redirect()->intended(route('accounting.dashboard'))
                ->with('success', 'Logged in to Accountant Portal.');
        }

        // 3. Try to login as Outlet (using outlet guard)
        if (Auth::guard('outlet')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $outlet = Auth::guard('outlet')->user();
            session(['portal_outlet_id' => $outlet->id]);

            return redirect()->intended(route('portal.dashboard'))
                ->with('success', "Logged in to {$outlet->name} portal.");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle Admin Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out of admin panel.');
    }

    /**
     * Handle Accountant Logout.
     */
    public function accountantLogout(Request $request)
    {
        Auth::guard('accountant')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out of Accountant Portal.');
    }
}
