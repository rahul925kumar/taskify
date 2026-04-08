@extends('layouts.app')
@section('title', $task->title)
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Task Details</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.tasks.index') }}">Tasks</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">{{ Str::limit($task->title, 30) }}</a></li>
        </ul>
    </div>

    <div class="row">
        {{-- Task Info --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">{{ $task->title }}</h4>
                        <div>
                            <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit me-1"></i>Edit</a>
                            <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" class="d-inline delete-form">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i>Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $statusColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                        $prioColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                    @endphp
                    <div class="row mb-3">
                        <div class="col-md-3"><strong>Assigned to:</strong><br>{{ $task->assignee->name ?? 'Unassigned' }}</div>
                        <div class="col-md-3"><strong>Initially assigned:</strong><br>{{ $task->originalAssignee?->name ?? '—' }}</div>
                        <div class="col-md-3"><strong>Status:</strong><br><span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></div>
                        <div class="col-md-3"><strong>Priority:</strong><br><span class="badge bg-{{ $prioColors[$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Start Date:</strong><br>{{ $task->start_date?->format('M d, Y') ?? '-' }}</div>
                        <div class="col-md-6"><strong>Due Date:</strong><br>
                            @if($task->due_date)
                                <span class="{{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $task->due_date->format('M d, Y') }}</span>
                                @if($task->isOverdue()) <span class="badge bg-danger">OVERDUE</span> @endif
                            @else - @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-12"><strong>Created By:</strong> {{ $task->creator->name }}</div>
                    </div>

                    @if($task->status === 'cancelled' && $task->cancellation_reason)
                        <div class="alert alert-danger py-2 mb-3">
                            <strong>Cancellation reason:</strong> {{ $task->cancellation_reason }}
                        </div>
                    @endif

                    @if($task->description)
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p class="mt-1">{{ $task->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Comments --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Comments</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tasks.comment', $task) }}" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Post Comment</button>
                    </form>
                    <hr>
                    @forelse($task->comments as $comment)
                        <div class="d-flex mb-3" id="comment-{{ $comment->id }}">
                            <div class="avatar avatar-sm me-3">
                                <img src="{{ $comment->user?->image ? asset('uploads/employees/' . $comment->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <strong>{{ $comment->user?->name ?? 'Deleted User' }}</strong>
                                <span class="text-muted ms-2" style="font-size:12px;">{{ $comment->created_at->diffForHumans() }}</span>
                                <p class="mb-1">{{ $comment->comment }}</p>
                                <a href="#" class="text-primary" style="font-size:12px;" onclick="event.preventDefault(); document.getElementById('reply-form-{{ $comment->id }}').classList.toggle('d-none');">Reply</a>

                                <form method="POST" action="{{ route('admin.tasks.comment', $task) }}" class="mt-2 d-none" id="reply-form-{{ $comment->id }}">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" placeholder="Write a reply..." required></textarea>
                                    <button type="submit" class="btn btn-xs btn-primary">Reply</button>
                                </form>

                                @foreach($comment->replies as $reply)
                                    <div class="d-flex mt-2 ms-4">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="{{ $reply->user?->image ? asset('uploads/employees/' . $reply->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                                        </div>
                                        <div>
                                            <strong>{{ $reply->user?->name ?? 'Deleted User' }}</strong>
                                            <span class="text-muted ms-1" style="font-size:11px;">{{ $reply->created_at->diffForHumans() }}</span>
                                            <p class="mb-0">{{ $reply->comment }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No comments yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- History --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Task History</h4></div>
                <div class="card-body">
                    @forelse($task->histories as $history)
                        <div class="d-flex align-items-start mb-2">
                            <i class="fas fa-circle text-primary me-2 mt-1" style="font-size:8px;"></i>
                            <div>
                                <strong>{{ $history->user?->name ?? 'Deleted User' }}</strong>
                                <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_',' ',$history->action)) }}</span>
                                <span class="text-muted" style="font-size:12px;">{{ $history->created_at->diffForHumans() }}</span>
                                @if($history->details)<br><small class="text-muted">{{ $history->details }}</small>@endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No history.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right Sidebar --}}
        <div class="col-md-4">
            {{-- Update Status --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Update Status</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.tasks.update-status', $task) }}" id="admin-task-status-form">
                        @csrf
                        @method('PATCH')
                        <label class="form-label">Status</label>
                        <select name="status" id="admin-task-status" class="form-select form-select-sm mb-2">
                            @foreach(config('constants.task_statuses') as $s)
                                <option value="{{ $s }}" {{ $task->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <div id="admin-cancel-reason-wrap" class="mb-2 {{ $task->status === 'cancelled' ? '' : 'd-none' }}">
                            <label class="form-label">Cancellation reason <span class="text-danger">*</span></label>
                            <textarea name="cancellation_reason" id="admin-cancel-reason" class="form-control form-control-sm" rows="2" placeholder="Required when setting status to Cancelled">{{ old('cancellation_reason', $task->cancellation_reason) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Update Status</button>
                    </form>
                </div>
            </div>

            {{-- Assignee & Reassign --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Assigned To</h4></div>
                <div class="card-body">
                    @if($task->assignee)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar me-3">
                                <img src="{{ $task->assignee->image ? asset('uploads/employees/' . $task->assignee->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                            </div>
                            <div>
                                <strong>{{ $task->assignee->name }}</strong>
                                <p class="text-muted mb-0" style="font-size:12px;">{{ $task->assignee->role }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">Unassigned</p>
                    @endif

                    <form method="POST" action="{{ route('admin.tasks.reassign', $task) }}">
                        @csrf
                        <div class="mb-2">
                            <select name="assigned_to" class="form-select form-select-sm" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ $task->assigned_to == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary w-100">Reassign Task</button>
                    </form>
                </div>
            </div>

            {{-- Attachments --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Attachments</h4></div>
                <div class="card-body">
                    @foreach($task->attachments as $att)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <i class="fas fa-paperclip me-1"></i>
                                <a href="{{ asset('uploads/attachments/' . $att->filename) }}" target="_blank">{{ $att->original_name }}</a>
                                <br><small class="text-muted">by {{ $att->uploader?->name ?? 'Deleted User' }} &middot; {{ $att->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('admin.tasks.attachment', $task) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="attachment" class="form-control" required>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script>
    (function () {
        var sel = document.getElementById('admin-task-status');
        var wrap = document.getElementById('admin-cancel-reason-wrap');
        var ta = document.getElementById('admin-cancel-reason');
        if (!sel || !wrap || !ta) return;
        function sync() {
            if (sel.value === 'cancelled') {
                wrap.classList.remove('d-none');
                ta.setAttribute('required', 'required');
            } else {
                wrap.classList.add('d-none');
                ta.removeAttribute('required');
            }
        }
        sel.addEventListener('change', sync);
        sync();
    })();

    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            swal({ title: "Delete this task?", icon: "warning", buttons: true, dangerMode: true })
                .then(ok => { if (ok) form.submit(); });
        });
    });
</script>
@endpush
