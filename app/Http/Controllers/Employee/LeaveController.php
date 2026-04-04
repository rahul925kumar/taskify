<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('employee.leaves.index', compact('leaves'));
    }

    public function create()
    {
        return view('employee.leaves.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'type' => 'required|in:sick,casual,earned,emergency,other',
            'reason' => 'required|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $leave = Leave::create($validated);

        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            $admin->notify(new TaskNotification(
                'Leave Request',
                auth()->user()->name . ' has requested ' . $leave->type . ' leave from ' . $leave->from_date->format('M d') . ' to ' . $leave->to_date->format('M d') . ' (' . $leave->totalDays() . ' days)',
                route('admin.leaves.show', $leave)
            ));
        }

        return redirect()->route('employee.leaves.index')->with('success', 'Leave request submitted successfully.');
    }
}
