<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalOutletSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!\Illuminate\Support\Facades\Auth::guard('outlet')->check()) {
            return redirect()->route('portal.login')->with('error', 'Please log in to your retail outlet portal.');
        }

        if (!$request->session()->has('portal_outlet_id')) {
            $request->session()->put('portal_outlet_id', \Illuminate\Support\Facades\Auth::guard('outlet')->id());
        }

        return $next($request);
    }
}
