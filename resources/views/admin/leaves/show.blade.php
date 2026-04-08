@extends('layouts.app')
@section('title', 'Leave Details')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Leave Details</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.leaves.index') }}">Leaves</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Details</a></li>
        </ul>
    </div>

    <div class="row">
        {{-- Leave Info --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title">Leave Information</h4></div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar me-3">
                            <img src="{{ $leave->user->image ? asset('uploads/employees/' . $leave->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                        </div>
                        <div>
                            <strong>{{ $leave->user->name }}</strong>
                            <p class="text-muted mb-0" style="font-size:12px;">{{ $leave->user->role }} &middot; {{ $leave->user->email }}</p>
                        </div>
                    </div>

                    @php $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; @endphp
                    <table class="table table-borderless mb-0" style="font-size:14px;">
                        <tr><td class="text-muted" width="100">Type</td><td><span class="badge bg-info">{{ ucfirst($leave->type) }}</span></td></tr>
                        <tr><td class="text-muted">From</td><td>{{ $leave->from_date->format('M d, Y') }}</td></tr>
                        <tr><td class="text-muted">To</td><td>{{ $leave->to_date->format('M d, Y') }}</td></tr>
                        <tr><td class="text-muted">Days</td><td><strong>{{ $leave->totalDays() }}</strong></td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-{{ $statusColors[$leave->status] }}">{{ ucfirst($leave->status) }}</span></td></tr>
                        <tr><td class="text-muted">Applied</td><td>{{ $leave->created_at->format('M d, Y h:i A') }}</td></tr>
                    </table>

                    @if($leave->delegate)
                        <div class="mt-2">
                            <strong>Suggested work delegate:</strong>
                            <p class="mt-1 mb-0">{{ $leave->delegate->name }} ({{ $leave->delegate->email }})</p>
                        </div>
                    @endif

                    <div class="mt-3">
                        <strong>Reason:</strong>
                        <p class="mt-1">{{ $leave->reason }}</p>
                    </div>

                    @if($leave->admin_remarks)
                        <div class="mt-2">
                            <strong>Admin Remarks:</strong>
                            <p class="mt-1 text-danger">{{ $leave->admin_remarks }}</p>
                        </div>
                    @endif

                    @if($leave->status === 'pending')
                        <div class="mt-3 d-flex gap-2">
                            <form action="{{ route('admin.leaves.approve', $leave) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-success"><i class="fas fa-check me-1"></i> Approve</button>
                            </form>
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times me-1"></i> Reject</button>
                        </div>

                        <div class="modal fade" id="rejectModal" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" action="{{ route('admin.leaves.reject', $leave) }}">
                                    @csrf @method('PATCH')
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Reject Leave</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body">
                                            <textarea name="admin_remarks" class="form-control" rows="3" placeholder="Reason for rejection (optional)"></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pending Tasks + Bulk Reassign --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-tasks me-1"></i> {{ $leave->user->name }}'s Pending Tasks ({{ $pendingTasks->count() }})
                    </h4>
                </div>
                <div class="card-body">
                    @if($pendingTasks->count() > 0)
                        <form method="POST" action="{{ route('admin.leaves.bulk-reassign', $leave) }}">
                            @csrf
                            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="checkbox" id="selectAll" class="form-check-input" style="cursor:pointer;">
                                    <label for="selectAll" class="mb-0 fw-bold" style="cursor:pointer;">Select All</label>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <label class="mb-0 me-1">Reassign to:</label>
                                    <select name="assigned_to" class="form-select form-select-sm" style="width:200px;" required>
                                        <option value="">Select Employee</option>
                                        @foreach($availableEmployees as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->role }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-exchange-alt me-1"></i> Reassign Selected</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr><th width="40"></th><th>Task</th><th>Status</th><th>Priority</th><th>Due Date</th></tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $statusColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                                            $prioColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                                        @endphp
                                        @foreach($pendingTasks as $task)
                                            <tr class="{{ $task->isOverdue() ? 'table-danger' : '' }}">
                                                <td><input type="checkbox" name="task_ids[]" value="{{ $task->id }}" class="form-check-input task-checkbox"></td>
                                                <td><a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 35) }}</a></td>
                                                <td><span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></td>
                                                <td><span class="badge bg-{{ $prioColors[$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span></td>
                                                <td>
                                                    @if($task->due_date)
                                                        {{ $task->due_date->format('M d, Y') }}
                                                        @if($task->isOverdue()) <i class="fas fa-exclamation-circle text-danger"></i> @endif
                                                    @else - @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size:48px;"></i>
                            <p class="mt-3 text-muted">No pending tasks. Nothing to reassign.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.task-checkbox').forEach(cb => cb.checked = this.checked);
    });
</script>
@endpush
