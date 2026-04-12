@extends('layouts.app')
@section('title', 'My Tasks')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">My Tasks</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('employee.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Tasks</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">My Tasks</h4>
                <a href="{{ route('employee.tasks.kanban') }}" class="btn btn-sm btn-primary"><i class="fas fa-columns me-1"></i>Kanban</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach(config('constants.task_statuses') as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priority</option>
                        @foreach(config('constants.task_priorities') as $p)
                            <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-info">Filter</button>
                    <a href="{{ route('employee.tasks.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>#</th><th>Title</th><th>Initially assigned</th><th>Status</th><th>Priority</th><th>Due Date</th><th>Days since created</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @php
                            $statusColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                            $prioColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                        @endphp
                        @forelse($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                <td>{{ $task->id }}</td>
                                <td><a href="{{ route('employee.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a></td>
                                <td>{{ $task->originalAssignee?->name ?? '—' }}</td>
                                <td><span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></td>
                                <td><span class="badge bg-{{ $prioColors[$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span></td>
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($task->isOverdue()) <i class="fas fa-exclamation-circle text-danger"></i> @endif
                                    @else - @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $task->daysSinceCreation() }} {{ Str::plural('day', $task->daysSinceCreation()) }}</span></td>
                                <td><a href="{{ route('employee.tasks.show', $task) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No tasks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $tasks->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
