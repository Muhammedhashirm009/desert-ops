<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // 1. Check if authenticated as outlet employee (new system)
        if (Auth::guard('outlet_employee')->check()) {
            $employee = Auth::guard('outlet_employee')->user();

            // Block inactive employees
            if (!$employee->is_active) {
                Auth::guard('outlet_employee')->logout();
                return redirect()->route('portal.login')->with('error', 'Your account has been deactivated. Please contact your outlet administrator.');
            }

            if (!$request->session()->has('portal_outlet_id')) {
                $request->session()->put('portal_outlet_id', $employee->outlet_id);
            }

            // Store employee info in session for role-based access
            $request->session()->put('portal_employee_id', $employee->id);
            $request->session()->put('portal_employee_role', $employee->role);
            $request->session()->put('portal_employee_name', $employee->name);

            return $next($request);
        }

        // 2. Fallback: Check legacy outlet guard (backward compatibility)
        if (Auth::guard('outlet')->check()) {
            if (!$request->session()->has('portal_outlet_id')) {
                $request->session()->put('portal_outlet_id', Auth::guard('outlet')->id());
            }

            // Legacy outlet login gets full admin-like access
            $request->session()->put('portal_employee_id', null);
            $request->session()->put('portal_employee_role', 'outlet_admin');
            $request->session()->put('portal_employee_name', Auth::guard('outlet')->user()->name . ' (Outlet)');

            return $next($request);
        }

        return redirect()->route('portal.login')->with('error', 'Please log in to your retail outlet portal.');
    }
}
