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
            <form method="GET" id="tasks-filter-form" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filter by status">
                        <option value="">All Status</option>
                        @foreach(config('constants.task_statuses') as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filter by priority">
                        <option value="">All Priority</option>
                        @foreach(config('constants.task_priorities') as $p)
                            <option value="{{ $p }}" {{ request('priority') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="assigned_to" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Filter by assignee">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-info">Filter</button>
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </div>
            </form>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            @endif

            <form id="bulk-delete-form" action="{{ route('admin.tasks.bulk-destroy') }}" method="POST" class="mb-0">
                @csrf
                @method('DELETE')
                <p class="text-muted small mb-2" id="bulk-selection-hint">Bulk delete: only <strong>completed</strong> or <strong>cancelled</strong> tasks can be selected.</p>
                <div id="bulk-delete-actions" class="d-none align-items-center flex-wrap gap-2 mb-3">
                    <span class="text-secondary small fw-semibold" id="bulk-selected-count"></span>
                    <button type="button" id="bulk-delete-btn" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete selected
                    </button>
                </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 2.5rem;">
                                <input type="checkbox" class="form-check-input" id="select-all-tasks" title="Select all on this page" aria-label="Select all on this page">
                            </th>
                            <th>#</th>
                            <th>Title</th>
                            <th>Assignee</th>
                            <th>Initially assigned</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th>Days since created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasks as $task)
                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                <td class="text-center align-middle">
                                    @if(in_array($task->status, ['completed', 'cancelled'], true))
                                        <input type="checkbox" class="form-check-input task-row-check" name="ids[]" value="{{ $task->id }}" aria-label="Select task {{ $task->id }}">
                                    @else
                                        <span class="text-muted small" title="Only completed or cancelled tasks can be deleted">—</span>
                                    @endif
                                </td>
                                <td>{{ $task->id }}</td>
                                <td><a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a></td>
                                <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                <td>{{ $task->originalAssignee?->name ?? '—' }}</td>
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
                                    <span class="badge bg-light text-dark border">{{ $task->daysSinceCreation() }} {{ Str::plural('day', $task->daysSinceCreation()) }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                    @if(in_array($task->status, ['completed', 'cancelled'], true))
                                        <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="d-inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Soft delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No tasks found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </form>
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

    (function() {
        const bulkForm = document.getElementById('bulk-delete-form');
        if (!bulkForm) return;
        const bulkBtn = document.getElementById('bulk-delete-btn');
        const bulkActions = document.getElementById('bulk-delete-actions');
        const bulkCountEl = document.getElementById('bulk-selected-count');
        const selectAll = document.getElementById('select-all-tasks');
        const rowChecks = () => Array.from(bulkForm.querySelectorAll('.task-row-check'));

        function updateBulkUi() {
            const checks = rowChecks();
            const n = checks.filter(c => c.checked).length;
            if (bulkActions) {
                bulkActions.classList.toggle('d-none', n === 0);
                bulkActions.classList.toggle('d-flex', n > 0);
            }
            if (bulkCountEl && n > 0) {
                bulkCountEl.textContent = n === 1 ? '1 task selected' : n + ' tasks selected';
            }
            if (selectAll) {
                selectAll.disabled = checks.length === 0;
                selectAll.checked = checks.length > 0 && n === checks.length;
                selectAll.indeterminate = n > 0 && n < checks.length;
            }
        }

        rowChecks().forEach(cb => cb.addEventListener('change', updateBulkUi));
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                rowChecks().forEach(cb => { cb.checked = selectAll.checked; });
                updateBulkUi();
            });
        }
        updateBulkUi();

        bulkBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const n = rowChecks().filter(c => c.checked).length;
            if (n === 0) return;
            swal({
                title: "Delete " + n + " task" + (n === 1 ? "" : "s") + "?",
                text: "Selected tasks will be soft deleted.",
                icon: "warning",
                buttons: true,
                dangerMode: true
            }).then(ok => { if (ok) bulkForm.submit(); });
        });
    })();
</script>
@endpush
