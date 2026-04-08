<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Leave;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees = User::where('is_admin', false)->count();
        $totalClients = Client::count();
        $openTasks = Task::whereNotIn('status', ['completed', 'cancelled'])->count();
        $totalTasks = Task::count();

        $tasksByStatus = Task::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $employees = User::where('is_admin', false)
            ->withCount([
                'assignedTasks as completed_tasks' => fn ($q) => $q->where('status', 'completed'),
                'assignedTasks as pending_tasks' => fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled']),
                'assignedTasks as total_tasks',
            ])
            ->get();

        $recentTasks = Task::with(['assignee', 'project', 'originalAssignee'])
            ->latest()
            ->take(10)
            ->get();

        $todayLogins = Attendance::with('user')
            ->whereDate('date', today())
            ->get();

        $overdueTasksCount = Task::where('due_date', '<', today())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->count();

        $pendingLeaves = Leave::where('status', 'pending')->with('user')->latest()->get();

        $onLeaveToday = User::where('is_admin', false)
            ->whereHas('leaves', fn ($q) => $q->where('status', 'approved')->where('from_date', '<=', today())->where('to_date', '>=', today()))
            ->withCount(['assignedTasks as pending_tasks' => fn ($q) => $q->whereNotIn('status', ['completed', 'cancelled'])])
            ->get();

        return view('admin.dashboard.index', compact(
            'totalEmployees', 'totalClients', 'openTasks', 'totalTasks',
            'tasksByStatus', 'employees', 'recentTasks', 'todayLogins', 'overdueTasksCount',
            'pendingLeaves', 'onLeaveToday'
        ));
    }
}
