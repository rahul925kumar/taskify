@extends('layouts.app')
@section('title', $task->title)
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Task Details</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('employee.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('employee.tasks.index') }}">Tasks</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">{{ Str::limit($task->title, 30) }}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $task->title }}</h4>
                </div>
                <div class="card-body">
                    @php
                        $statusColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
                        $prioColors = ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'];
                    @endphp
                    @php $isAssignee = (int) $task->assigned_to === (int) auth()->id(); @endphp
                    @if(! $isAssignee && (int) $task->created_by === (int) auth()->id())
                        <div class="alert alert-info py-2 mb-3">
                            You created this task. Only the <strong>assignee</strong> can change status, add comments, or upload attachments.
                        </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Status:</strong><br><span class="badge bg-{{ $statusColors[$task->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span></div>
                        <div class="col-md-4"><strong>Priority:</strong><br><span class="badge bg-{{ $prioColors[$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span></div>
                        <div class="col-md-4"><strong>Assignee:</strong><br>{{ $task->assignee->name ?? 'Unassigned' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Initially assigned:</strong><br>{{ $task->originalAssignee?->name ?? '—' }}</div>
                        <div class="col-md-4"><strong>Start Date:</strong><br>{{ $task->start_date?->format('M d, Y') ?? '-' }}</div>
                        <div class="col-md-4"><strong>Due Date:</strong><br>
                            @if($task->due_date)
                                <span class="{{ $task->isOverdue() ? 'text-danger fw-bold' : '' }}">{{ $task->due_date->format('M d, Y') }}</span>
                                @if($task->isOverdue()) <span class="badge bg-danger">OVERDUE</span> @endif
                            @else - @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4"><strong>Created By:</strong><br>{{ $task->creator->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Created:</strong><br>{{ $task->created_at->format('M d, Y h:i A') }}</div>
                        <div class="col-md-6"><strong>Days since created:</strong><br>
                            <span class="badge bg-info text-dark fs-6">{{ $task->daysSinceCreation() }} {{ Str::plural('day', $task->daysSinceCreation()) }}</span>
                            <span class="text-muted small ms-1">(from creation date)</span>
                        </div>
                    </div>
                    @if($task->status === 'cancelled' && $task->cancellation_reason)
                        <div class="alert alert-danger py-2 mb-3">
                            <strong>Cancellation reason:</strong> {{ $task->cancellation_reason }}
                        </div>
                    @endif
                    @if($task->description)
                        <div class="mb-3"><strong>Description:</strong><p class="mt-1">{{ $task->description }}</p></div>
                    @endif
                </div>
            </div>

            {{-- Update Status --}}
            @if($isAssignee)
            <div class="card">
                <div class="card-header"><h4 class="card-title">Update Status</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('employee.tasks.update-status', $task) }}" class="d-flex gap-2">
                        @csrf @method('PATCH')
                        <select name="status" class="form-select" style="width: 200px;">
                            @foreach(config('constants.task_statuses') as $s)
                                <option value="{{ $s }}" {{ $task->status == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Comments</h4></div>
                <div class="card-body">
                    @if($isAssignee)
                    <form method="POST" action="{{ route('employee.tasks.comment', $task) }}" class="mb-4">
                        @csrf
                        <textarea name="comment" class="form-control mb-2" rows="3" placeholder="Add a comment..." required></textarea>
                        <button type="submit" class="btn btn-sm btn-primary">Post Comment</button>
                    </form>
                    <hr>
                    @endif
                    @forelse($task->comments as $comment)
                        <div class="d-flex mb-3">
                            <div class="avatar avatar-sm me-3">
                                <img src="{{ $comment->user?->image ? asset('uploads/employees/' . $comment->user->image) : asset('assets/img/kaiadmin/favicon.ico') }}" class="avatar-img rounded-circle">
                            </div>
                            <div class="flex-grow-1">
                                <strong>{{ $comment->user?->name ?? 'Deleted User' }}</strong>
                                <span class="text-muted ms-2" style="font-size:12px;">{{ $comment->created_at->diffForHumans() }}</span>
                                <p class="mb-1">{{ $comment->comment }}</p>
                                @if($isAssignee)
                                <a href="#" class="text-primary" style="font-size:12px;" onclick="event.preventDefault(); document.getElementById('reply-{{ $comment->id }}').classList.toggle('d-none');">Reply</a>

                                <form method="POST" action="{{ route('employee.tasks.comment', $task) }}" class="mt-2 d-none" id="reply-{{ $comment->id }}">
                                    @csrf
                                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                    <textarea name="comment" class="form-control form-control-sm mb-1" rows="2" required></textarea>
                                    <button type="submit" class="btn btn-xs btn-primary">Reply</button>
                                </form>
                                @endif

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
            {{-- Attachments --}}
            <div class="card">
                <div class="card-header"><h4 class="card-title">Attachments</h4></div>
                <div class="card-body">
                    @foreach($task->attachments as $att)
                        <div class="mb-2">
                            <i class="fas fa-paperclip me-1"></i>
                            <a href="{{ asset('uploads/attachments/' . $att->filename) }}" target="_blank">{{ $att->original_name }}</a>
                            <br><small class="text-muted">by {{ $att->uploader?->name ?? 'Deleted User' }} &middot; {{ $att->created_at->diffForHumans() }}</small>
                        </div>
                    @endforeach

                    @if($isAssignee)
                    <form method="POST" action="{{ route('employee.tasks.attachment', $task) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="input-group input-group-sm">
                            <input type="file" name="attachment" class="form-control" required>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
