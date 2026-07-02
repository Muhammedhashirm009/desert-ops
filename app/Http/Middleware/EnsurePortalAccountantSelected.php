<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsurePortalAccountantSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('accountant')->check()) {
            if (!$request->session()->has('portal_accountant_id')) {
                $request->session()->put('portal_accountant_id', Auth::guard('accountant')->id());
            }
            return $next($request);
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->role === 'admin' || $user->role === 'accountant') {
                if (!$request->session()->has('portal_accountant_id')) {
                    $request->session()->put('portal_accountant_id', $user->id);
                }
                return $next($request);
            }
            abort(403, 'Unauthorized action.');
        }

        return redirect()->route('login')->with('error', 'Please log in to the Accountant Portal.');
    }
}
