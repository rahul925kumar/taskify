@extends('layouts.app')
@section('title', 'Leave Management')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Leave Management</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Leaves</a></li>
        </ul>
    </div>

    {{-- On Leave Today Alert --}}
    @if($onLeaveToday->count() > 0)
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning">
                <h4 class="card-title mb-0"><i class="fas fa-user-clock me-2"></i> Employees On Leave Today ({{ $onLeaveToday->count() }})</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($onLeaveToday as $emp)
                        <div class="col-md-4 mb-3">
                            <div class="card border h-100">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="{{ $emp->image ? asset('uploads/employees/' . $emp->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                                        </div>
                                        <div>
                                            <strong>{{ $emp->name }}</strong>
                                            <br><small class="text-muted">{{ $emp->role }}</small>
                                        </div>
                                    </div>
                                    @php $activeLeave = $emp->leaves->first(); @endphp
                                    <p class="mb-1" style="font-size:12px;">
                                        <span class="badge bg-info">{{ ucfirst($activeLeave->type) }}</span>
                                        {{ $activeLeave->from_date->format('M d') }} - {{ $activeLeave->to_date->format('M d') }}
                                    </p>
                                    <p class="mb-2" style="font-size:12px;">
                                        <span class="text-danger fw-bold">{{ $emp->pending_tasks }}</span> pending task(s)
                                    </p>
                                    @if($emp->pending_tasks > 0)
                                        <a href="{{ route('admin.leaves.show', $activeLeave) }}" class="btn btn-sm btn-outline-primary w-100">
                                            <i class="fas fa-exchange-alt me-1"></i> View & Reassign Tasks
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Leave Requests Table --}}
    <div class="card">
        <div class="card-header"><h4 class="card-title">All Leave Requests</h4></div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-sm btn-info">Filter</button>
                    <a href="{{ route('admin.leaves.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        @php $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; @endphp
                        @forelse($leaves as $leave)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="{{ $leave->user->image ? asset('uploads/employees/' . $leave->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                                        </div>
                                        {{ $leave->user->name }}
                                    </div>
                                </td>
                                <td><span class="badge bg-info">{{ ucfirst($leave->type) }}</span></td>
                                <td>{{ $leave->from_date->format('M d, Y') }}</td>
                                <td>{{ $leave->to_date->format('M d, Y') }}</td>
                                <td>{{ $leave->totalDays() }}</td>
                                <td>{{ Str::limit($leave->reason, 30) }}</td>
                                <td><span class="badge bg-{{ $statusColors[$leave->status] }}">{{ ucfirst($leave->status) }}</span></td>
                                <td>
                                    <a href="{{ route('admin.leaves.show', $leave) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    @if($leave->status === 'pending')
                                        <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST" class="d-inline">
                                            @csrf @method('PATCH')
                                            <button class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                                        </form>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}"><i class="fas fa-times"></i></button>
                                        {{-- Reject Modal --}}
                                        <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="modal-content">
                                                        <div class="modal-header"><h5 class="modal-title">Reject Leave - {{ $leave->user->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                                        <div class="modal-body">
                                                            <label class="form-label">Reason for rejection (optional)</label>
                                                            <textarea name="admin_remarks" class="form-control" rows="3" placeholder="Enter remarks..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-danger">Reject Leave</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted">No leave requests.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $leaves->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
