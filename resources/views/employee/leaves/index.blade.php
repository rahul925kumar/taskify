@extends('layouts.app')
@section('title', 'My Leaves')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">My Leaves</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('employee.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Leaves</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">Leave History</h4>
                <a href="{{ route('employee.leaves.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Apply Leave</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>#</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Delegate</th><th>Reason</th><th>Status</th><th>Admin Remarks</th><th>Applied On</th></tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            @php
                                $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                            @endphp
                            <tr>
                                <td>{{ $leave->id }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($leave->type) }}</span></td>
                                <td>{{ $leave->from_date->format('M d, Y') }}</td>
                                <td>{{ $leave->to_date->format('M d, Y') }}</td>
                                <td>{{ $leave->totalDays() }}</td>
                                <td>{{ $leave->delegate?->name ?? '—' }}</td>
                                <td>{{ Str::limit($leave->reason, 40) }}</td>
                                <td><span class="badge bg-{{ $statusColors[$leave->status] }}">{{ ucfirst($leave->status) }}</span></td>
                                <td>{{ $leave->admin_remarks ?? '-' }}</td>
                                <td>{{ $leave->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted">No leave records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $leaves->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
