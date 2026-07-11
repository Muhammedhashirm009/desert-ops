<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Models\OutletEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class OutletEmployeeController extends Controller
{
    /**
     * Check if current session is outlet_admin.
     */
    private function ensureAdmin()
    {
        if (session('portal_employee_role') !== 'outlet_admin') {
            abort(403, 'Only outlet administrators can manage employees.');
        }
    }

    /**
     * Display a listing of employees for this outlet.
     */
    public function index()
    {
        $this->ensureAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);
        $employees = OutletEmployee::where('outlet_id', $outletId)
            ->orderBy('name', 'asc')
            ->get();

        return view('portal.employees.index', compact('outlet', 'employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $this->ensureAdmin();
        $outletId = session('portal_outlet_id');
        $outlet = Outlet::findOrFail($outletId);

        $roles = [
            'outlet_admin' => 'Outlet Admin (Full Portal Access)',
            'salesperson' => 'Salesperson (Sales & Showcase Requests Only)',
        ];

        return view('portal.employees.create', compact('outlet', 'roles'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();
        $outletId = session('portal_outlet_id');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:outlet_employees'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['outlet_admin', 'salesperson'])],
        ]);

        OutletEmployee::create([
            'outlet_id' => $outletId,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'is_active' => true,
        ]);

        return redirect()->route('portal.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(OutletEmployee $employee)
    {
        $this->ensureAdmin();
        $outletId = session('portal_outlet_id');

        if ($employee->outlet_id != $outletId) {
            abort(403, 'Unauthorized access to this employee.');
        }

        $roles = [
            'outlet_admin' => 'Outlet Admin (Full Portal Access)',
            'salesperson' => 'Salesperson (Sales & Showcase Requests Only)',
        ];

        return view('portal.employees.edit', compact('employee', 'roles'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, OutletEmployee $employee)
    {
        $this->ensureAdmin();
        $outletId = session('portal_outlet_id');

        if ($employee->outlet_id != $outletId) {
            abort(403, 'Unauthorized access to this employee.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('outlet_employees')->ignore($employee->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['outlet_admin', 'salesperson'])],
            'is_active' => ['required', 'boolean'],
        ]);

        $employee->name = $data['name'];
        $employee->email = $data['email'];
        $employee->role = $data['role'];
        $employee->is_active = $data['is_active'];

        if (!empty($data['password'])) {
            $employee->password = Hash::make($data['password']);
        }

        $employee->save();

        return redirect()->route('portal.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(OutletEmployee $employee)
    {
        $this->ensureAdmin();
        $outletId = session('portal_outlet_id');

        if ($employee->outlet_id != $outletId) {
            abort(403, 'Unauthorized access to this employee.');
        }

        // Don't allow self-deletion
        if (session('portal_employee_id') == $employee->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $employee->delete();

        return redirect()->route('portal.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
