@extends('layouts.app')
@section('title', 'Attendance Report')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Attendance Report</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Attendance</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">Attendance - {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</h4>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-info">Filter</button>
                    <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary ms-2">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="{{ $att->user->image ? asset('uploads/employees/' . $att->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                                        </div>
                                        {{ $att->user->name }}
                                    </div>
                                </td>
                                <td>{{ $att->date->format('M d, Y') }}</td>
                                <td>{{ $att->login_at->format('h:i A') }}</td>
                                <td>{{ $att->logout_at ? $att->logout_at->format('h:i A') : '-' }}</td>
                                <td>
                                    @if($att->logout_at)
                                        {{ $att->login_at->diff($att->logout_at)->format('%Hh %Im') }}
                                    @else
                                        <span class="text-muted">In progress</span>
                                    @endif
                                </td>
                                <td>
                                    @if($att->logout_at)
                                        <span class="badge bg-secondary">Completed</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No attendance records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $attendances->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
