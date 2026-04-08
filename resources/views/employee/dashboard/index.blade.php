@extends('layouts.app')
@section('title', 'Employee Dashboard')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">My Dashboard</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('employee.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Dashboard</a></li>
        </ul>
    </div>

    {{-- Stats --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-tasks"></i></div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers"><p class="card-category">Total Tasks</p><h4 class="card-title">{{ $totalCount }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-clock"></i></div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers"><p class="card-category">Pending</p><h4 class="card-title">{{ $pendingCount }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-check-circle"></i></div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers"><p class="card-category">Completed</p><h4 class="card-title">{{ $completedCount }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers"><p class="card-category">Overdue</p><h4 class="card-title">{{ $overdueCount }}</h4></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($overdueTasks->count() > 0)
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h4 class="card-title text-white">Overdue Tasks</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Task</th><th>Due Date</th><th>Priority</th><th>Action</th></tr></thead>
                        <tbody>
                            @foreach($overdueTasks as $task)
                                <tr class="table-danger">
                                    <td>{{ $task->title }}</td>
                                    <td>{{ $task->due_date->format('M d, Y') }}</td>
                                    <td><span class="badge bg-{{ ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'][$task->priority] }}">{{ ucfirst($task->priority) }}</span></td>
                                    <td><a href="{{ route('employee.tasks.show', $task) }}" class="btn btn-sm btn-info">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">Recent Tasks</h4>
                <a href="{{ route('employee.tasks.kanban') }}" class="btn btn-sm btn-primary"><i class="fas fa-columns me-1"></i>Kanban Board</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Task</th><th>Status</th><th>Priority</th><th>Due Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentTasks as $task)
                            @php
                                $statusColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                            @endphp
                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                <td><a href="{{ route('employee.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a></td>
                                <td><span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></td>
                                <td><span class="badge bg-{{ ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'][$task->priority] }}">{{ ucfirst($task->priority) }}</span></td>
                                <td>{{ $task->due_date?->format('M d, Y') ?? '-' }}</td>
                                <td><a href="{{ route('employee.tasks.show', $task) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">No tasks assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
