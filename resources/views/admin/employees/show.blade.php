@extends('layouts.app')
@section('title', $employee->name)
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Employee Profile</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">{{ $employee->name }}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xxl mx-auto mb-3">
                        <img src="{{ $employee->image ? asset('uploads/employees/' . $employee->image) : asset('assets/img/kaiadmin/favicon.ico') }}"
                             alt=""
                             class="avatar-img rounded-circle"
                             style="object-fit: cover;">
                    </div>
                    <h4 class="fw-bold mb-1">{{ $employee->name }}</h4>
                    <p class="text-muted mb-2">{{ $employee->role }}</p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <form method="POST"
                              action="{{ route('admin.employees.destroy', $employee) }}"
                              class="d-inline delete-employee-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Contact</div>
                </div>
                <div class="card-body">
                    <p class="mb-2"><i class="fas fa-envelope text-muted me-2"></i>{{ $employee->email }}</p>
                    <p class="mb-2"><i class="fas fa-phone text-muted me-2"></i>{{ $employee->phone ?? '—' }}</p>
                    <p class="mb-0"><i class="fas fa-map-marker-alt text-muted me-2"></i>{{ $employee->address ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-sm-6 col-md-3 mb-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Total tasks</p>
                                        <h4 class="card-title">{{ $employee->total_tasks }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 mb-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Completed</p>
                                        <h4 class="card-title">{{ $employee->completed_tasks }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 mb-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Pending</p>
                                        <h4 class="card-title">{{ $employee->pending_tasks }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 mb-4">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-danger bubble-shadow-small">
                                        <i class="fas fa-exclamation-circle"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Overdue</p>
                                        <h4 class="card-title">{{ $employee->overdue_tasks }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="card-title">Recent tasks</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 40) }}</a>
                                        </td>
                                        <td>{{ $task->project->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'warning') }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>
                                        </td>
                                        <td>{{ $task->due_date ? $task->due_date->format('M j, Y') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No tasks assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Recent attendance</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Login</th>
                                    <th>Logout</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendance as $row)
                                    <tr>
                                        <td>{{ $row->date?->format('M j, Y') ?? '—' }}</td>
                                        <td>{{ $row->login_at ? $row->login_at->format('h:i A') : '—' }}</td>
                                        <td>{{ $row->logout_at ? $row->logout_at->format('h:i A') : '—' }}</td>
                                        <td>
                                            @if($row->logout_at)
                                                <span class="badge bg-secondary">Completed</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No attendance records yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script>
        document.querySelectorAll('.delete-employee-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                swal({
                    title: 'Delete this employee?',
                    text: 'This will remove the employee record. You can confirm to continue.',
                    icon: 'warning',
                    buttons: ['Cancel', 'Delete'],
                    dangerMode: true,
                }).then(function (willDelete) {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
