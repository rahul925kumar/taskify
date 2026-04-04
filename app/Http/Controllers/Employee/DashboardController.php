<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $pendingTasks = Task::where('assigned_to', $user->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('project')
            ->latest()
            ->get();

        $overdueTasks = Task::where('assigned_to', $user->id)
            ->where('due_date', '<', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with('project')
            ->get();

        $completedCount = Task::where('assigned_to', $user->id)->where('status', 'completed')->count();
        $totalCount = Task::where('assigned_to', $user->id)->count();
        $pendingCount = $pendingTasks->count();
        $overdueCount = $overdueTasks->count();

        $recentTasks = Task::where('assigned_to', $user->id)
            ->with('project')
            ->latest('updated_at')
            ->take(10)
            ->get();

        return view('employee.dashboard.index', compact(
            'pendingTasks', 'overdueTasks', 'completedCount',
            'totalCount', 'pendingCount', 'overdueCount', 'recentTasks'
        ));
    }
}
