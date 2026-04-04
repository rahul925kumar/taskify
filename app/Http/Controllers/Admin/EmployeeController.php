<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = User::where('is_admin', false)
            ->withCount([
                'assignedTasks as total_tasks',
                'assignedTasks as completed_tasks' => fn($q) => $q->where('status', 'completed'),
                'assignedTasks as pending_tasks' => fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']),
            ])
            ->latest()
            ->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function create()
    {
        return view('admin.employees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $validated['password'] = Hash::make('employee123');
        $validated['is_admin'] = false;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/employees'), $filename);
            $validated['image'] = $filename;
        }

        User::create($validated);

        return redirect()->route('admin.employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(User $employee)
    {
        $employee->loadCount([
            'assignedTasks as total_tasks',
            'assignedTasks as completed_tasks' => fn($q) => $q->where('status', 'completed'),
            'assignedTasks as pending_tasks' => fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']),
            'assignedTasks as overdue_tasks' => fn($q) => $q->where('due_date', '<', today())
                ->whereNotIn('status', ['completed', 'cancelled']),
        ]);

        $recentTasks = $employee->assignedTasks()->with('project')->latest()->take(10)->get();
        $recentAttendance = $employee->attendances()->latest()->take(30)->get();

        return view('admin.employees.show', compact('employee', 'recentTasks', 'recentAttendance'));
    }

    public function edit(User $employee)
    {
        return view('admin.employees.edit', compact('employee'));
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($employee->image && file_exists(public_path('uploads/employees/' . $employee->image))) {
                unlink(public_path('uploads/employees/' . $employee->image));
            }
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/employees'), $filename);
            $validated['image'] = $filename;
        }

        $employee->update($validated);

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee)
    {
        $employee->delete();
        return redirect()->route('admin.employees.index')->with('success', 'Employee soft deleted successfully.');
    }
}
