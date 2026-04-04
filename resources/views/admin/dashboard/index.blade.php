@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Dashboard</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Dashboard</a></li>
        </ul>
    </div>

    {{-- On Leave Today Alert --}}
    @if($onLeaveToday->count() > 0)
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-user-clock me-3" style="font-size:24px;"></i>
            <div class="flex-grow-1">
                <strong>{{ $onLeaveToday->count() }} employee(s) on leave today:</strong>
                @foreach($onLeaveToday as $emp)
                    <span class="badge bg-warning text-dark ms-1">{{ $emp->name }} ({{ $emp->pending_tasks }} pending tasks)</span>
                @endforeach
            </div>
            <a href="{{ route('admin.leaves.index') }}" class="btn btn-sm btn-warning ms-3">Manage Leaves</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pending Leave Requests --}}
    @if($pendingLeaves->count() > 0)
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
            <i class="fas fa-calendar-minus me-3" style="font-size:24px;"></i>
            <div class="flex-grow-1">
                <strong>{{ $pendingLeaves->count() }} pending leave request(s):</strong>
                @foreach($pendingLeaves as $leave)
                    <span class="badge bg-info text-dark ms-1">{{ $leave->user->name }} ({{ $leave->from_date->format('M d') }} - {{ $leave->to_date->format('M d') }})</span>
                @endforeach
            </div>
            <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" class="btn btn-sm btn-info ms-3">Review</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-primary bubble-shadow-small">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Employees</p>
                                <h4 class="card-title">{{ $totalEmployees }}</h4>
                            </div>
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
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Projects</p>
                                <h4 class="card-title">{{ $totalProjects }}</h4>
                            </div>
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
                            <div class="icon-big text-center icon-success bubble-shadow-small">
                                <i class="fas fa-tasks"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Total Tasks</p>
                                <h4 class="card-title">{{ $totalTasks }}</h4>
                            </div>
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
                            <div class="icon-big text-center icon-danger bubble-shadow-small">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Overdue</p>
                                <h4 class="card-title">{{ $overdueTasksCount }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Task Status Chart --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Tasks by Status</div>
                </div>
                <div class="card-body">
                    <canvas id="taskStatusChart" height="250"></canvas>
                </div>
            </div>
        </div>

        {{-- Employee Performance Chart --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Employee Task Performance</div>
                </div>
                <div class="card-body">
                    <canvas id="employeeChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Recent Tasks --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Recent Tasks</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Project</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                    <tr>
                                        <td><a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 30) }}</a></td>
                                        <td>{{ $task->project->name ?? '-' }}</td>
                                        <td>{{ $task->assignee->name ?? 'Unassigned' }}</td>
                                        <td><span class="badge bg-{{ $task->status === 'completed' ? 'success' : ($task->status === 'in_progress' ? 'primary' : 'warning') }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></td>
                                        <td><span class="badge bg-{{ $task->priority === 'urgent' ? 'danger' : ($task->priority === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($task->priority) }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No tasks yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today's Logins --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Today's Logins</div>
                </div>
                <div class="card-body">
                    @forelse($todayLogins as $login)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm me-3">
                                <img src="{{ $login->user->image ? asset('uploads/employees/' . $login->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" alt="" class="avatar-img rounded-circle">
                            </div>
                            <div>
                                <strong>{{ $login->user->name }}</strong>
                                <p class="text-muted mb-0" style="font-size: 12px;">
                                    In: {{ $login->login_at->format('h:i A') }}
                                    @if($login->logout_at)
                                        | Out: {{ $login->logout_at->format('h:i A') }}
                                    @else
                                        | <span class="text-success">Active</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No logins today.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
<script>
    var statusCtx = document.getElementById('taskStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_map(fn($s) => ucfirst(str_replace('_', ' ', $s)), array_keys($tasksByStatus))) !!},
            datasets: [{
                data: {!! json_encode(array_values($tasksByStatus)) !!},
                backgroundColor: ['#ffc107', '#1572e8', '#6861ce', '#31ce36', '#f25961', '#48abf7'],
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    var empCtx = document.getElementById('employeeChart').getContext('2d');
    new Chart(empCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($employees->pluck('name')) !!},
            datasets: [
                { label: 'Completed', data: {!! json_encode($employees->pluck('completed_tasks')) !!}, backgroundColor: '#31ce36' },
                { label: 'Pending', data: {!! json_encode($employees->pluck('pending_tasks')) !!}, backgroundColor: '#ffc107' },
            ]
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } } }
    });
</script>
@endpush
