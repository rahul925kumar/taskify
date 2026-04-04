<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::where('is_admin', false)
            ->withCount([
                'assignedTasks as total_tasks',
                'assignedTasks as completed_tasks' => fn($q) => $q->where('status', 'completed'),
                'assignedTasks as pending_tasks' => fn($q) => $q->whereNotIn('status', ['completed', 'cancelled']),
                'assignedTasks as overdue_tasks' => fn($q) => $q->where('due_date', '<', today())
                    ->whereNotIn('status', ['completed', 'cancelled']),
            ])
            ->orderBy('name')
            ->get();

        $tasksByPriority = Task::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        $tasksByType = Task::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $tasksByCategory = Task::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $monthlyTasks = Task::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total'),
            DB::raw('SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        return view('admin.reports.index', compact('employees', 'tasksByPriority', 'tasksByType', 'tasksByCategory', 'monthlyTasks'));
    }
}
