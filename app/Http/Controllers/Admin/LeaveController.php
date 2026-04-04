<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\User;
use App\Notifications\TaskNotification;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = Leave::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        $leaves = $query->paginate(15)->withQueryString();
        $employees = User::where('is_admin', false)->orderBy('name')->get();

        $onLeaveToday = User::where('is_admin', false)
            ->whereHas('leaves', function ($q) {
                $q->where('status', 'approved')
                    ->where('from_date', '<=', today())
                    ->where('to_date', '>=', today());
            })
            ->with(['leaves' => function ($q) {
                $q->where('status', 'approved')
                    ->where('from_date', '<=', today())
                    ->where('to_date', '>=', today());
            }])
            ->withCount([
                'assignedTasks as pending_tasks' => fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']),
            ])
            ->get();

        return view('admin.leaves.index', compact('leaves', 'employees', 'onLeaveToday'));
    }

    public function show(Leave $leave)
    {
        $leave->load('user');

        $pendingTasks = Task::where('assigned_to', $leave->user_id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('project')
            ->get();

        $availableEmployees = User::where('is_admin', false)
            ->where('id', '!=', $leave->user_id)
            ->whereDoesntHave('leaves', function ($q) use ($leave) {
                $q->where('status', 'approved')
                    ->where('from_date', '<=', $leave->to_date)
                    ->where('to_date', '>=', $leave->from_date);
            })
            ->orderBy('name')
            ->get();

        return view('admin.leaves.show', compact('leave', 'pendingTasks', 'availableEmployees'));
    }

    public function approve(Leave $leave)
    {
        $leave->update(['status' => 'approved']);

        $leave->user->notify(new TaskNotification(
            'Leave Approved',
            'Your ' . $leave->type . ' leave from ' . $leave->from_date->format('M d') . ' to ' . $leave->to_date->format('M d') . ' has been approved.',
            route('employee.leaves.index')
        ));

        return back()->with('success', 'Leave approved.');
    }

    public function reject(Request $request, Leave $leave)
    {
        $request->validate(['admin_remarks' => 'nullable|string|max:500']);

        $leave->update([
            'status' => 'rejected',
            'admin_remarks' => $request->admin_remarks,
        ]);

        $leave->user->notify(new TaskNotification(
            'Leave Rejected',
            'Your ' . $leave->type . ' leave from ' . $leave->from_date->format('M d') . ' to ' . $leave->to_date->format('M d') . ' has been rejected.' . ($request->admin_remarks ? ' Reason: ' . $request->admin_remarks : ''),
            route('employee.leaves.index')
        ));

        return back()->with('success', 'Leave rejected.');
    }

    public function bulkReassign(Request $request, Leave $leave)
    {
        $request->validate([
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'exists:tasks,id',
            'assigned_to' => 'required|exists:users,id',
        ]);

        $newAssignee = User::findOrFail($request->assigned_to);
        $tasks = Task::whereIn('id', $request->task_ids)->get();

        foreach ($tasks as $task) {
            $oldName = $task->assignee ? $task->assignee->name : 'Unassigned';
            $task->update(['assigned_to' => $request->assigned_to]);

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'action' => 'reassigned',
                'details' => "Reassigned from {$oldName} (on leave) to {$newAssignee->name}",
            ]);
        }

        $newAssignee->notify(new TaskNotification(
            'Tasks Reassigned',
            count($tasks) . ' task(s) have been reassigned to you while ' . $leave->user->name . ' is on leave.',
            route('employee.tasks.index')
        ));

        return back()->with('success', count($tasks) . ' task(s) reassigned to ' . $newAssignee->name . '.');
    }
}
