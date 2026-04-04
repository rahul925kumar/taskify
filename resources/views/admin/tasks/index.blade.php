@extends('layouts.app')
@section('title', 'All Tasks')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">All Tasks</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Tasks</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">Tasks</h4>
                <a href="{{ route('admin.tasks.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> New Task</a>
            </div>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-2">
                    <select name="project_id" class="form-select form-select-sm">
                        <option value="">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        @foreach(config('constants.task_statuses') as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priority</option>
                        @foreach(config('constants.task_priorities') as $p)
                            <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="assigned_to" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-info">Filter</button>
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Assignee</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                <td>{{ $task->id }}</td>
                                <td><a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a></td>
                                <td>{{ $task->project->name ?? '-' }}</td>
                                <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                <td>
                                    @php
                                        $statusColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span>
                                </td>
                                <td>
                                    @php
                                        $prioColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                                    @endphp
                                    <span class="badge bg-{{ $prioColors[$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span>
                                </td>
                                <td>
                                    @if($task->due_date)
                                        {{ $task->due_date->format('M d, Y') }}
                                        @if($task->isOverdue()) <i class="fas fa-exclamation-circle text-danger"></i> @endif
                                    @else - @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="d-inline delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
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

@push('scripts')
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script>
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            swal({ title: "Delete this task?", text: "This is a soft delete.", icon: "warning", buttons: true, dangerMode: true })
                .then(ok => { if (ok) form.submit(); });
        });
    });
</script>
@endpush
