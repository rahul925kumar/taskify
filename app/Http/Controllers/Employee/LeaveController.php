<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\User;
use App\Notifications\TaskNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = Leave::where('user_id', auth()->id())
            ->with('delegate')
            ->latest()
            ->paginate(15);

        return view('employee.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $employees = User::where('is_admin', false)
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->get();

        return view('employee.leaves.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'type' => 'required|in:sick,casual,earned,emergency,other',
            'reason' => 'required|string|max:1000',
            'delegated_to' => 'nullable|exists:users,id',
        ]);

        if (! empty($validated['delegated_to'])) {
            if ((int) $validated['delegated_to'] === (int) auth()->id()) {
                return back()->withErrors(['delegated_to' => 'You cannot delegate work to yourself.'])->withInput();
            }

            $delegate = User::where('is_admin', false)->find($validated['delegated_to']);
            if (! $delegate) {
                return back()->withErrors(['delegated_to' => 'Invalid employee selected.'])->withInput();
            }

            $from = Carbon::parse($validated['from_date'])->startOfDay();
            $to = Carbon::parse($validated['to_date'])->startOfDay();

            if ($delegate->hasApprovedLeaveOverlapping($from, $to)) {
                return back()->withErrors([
                    'delegated_to' => 'This employee has approved leave overlapping your dates and cannot take over your work.',
                ])->withInput();
            }
        }

        $validated['user_id'] = auth()->id();
        $leave = Leave::create($validated);

        $admin = User::where('is_admin', true)->first();
        if ($admin) {
            $msg = auth()->user()->name.' has requested '.$leave->type.' leave from '.$leave->from_date->format('M d').' to '.$leave->to_date->format('M d').' ('.$leave->totalDays().' days)';
            if ($leave->delegated_to && $leave->delegate) {
                $msg .= '. Work suggested delegate: '.$leave->delegate->name;
            }
            $admin->notify(new TaskNotification(
                'Leave Request',
                $msg,
                route('admin.leaves.show', $leave)
            ));
        }

        return redirect()->route('employee.leaves.index')->with('success', 'Leave request submitted successfully.');
    }
}
