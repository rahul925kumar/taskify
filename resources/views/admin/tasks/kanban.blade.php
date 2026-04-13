@extends('layouts.app')
@section('title', 'Kanban Board')
@push('styles')
<style>
    .kanban-board { display: flex; gap: 15px; overflow-x: auto; padding-bottom: 20px; }
    .kanban-column { min-width: 280px; max-width: 300px; flex-shrink: 0; }
    .kanban-column .card { min-height: 400px; }
    .kanban-column .card-header { padding: 10px 15px; }
    .kanban-task { background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 12px; margin-bottom: 10px; cursor: grab; transition: box-shadow 0.2s; color: #111; }
    .kanban-task:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .kanban-task .task-title { font-weight: 600; font-size: 16px; margin-bottom: 6px; }
    .kanban-task .task-title a { color: #111; }
    .kanban-task .task-title .btn-link { color: #111 !important; }
    .kanban-task .task-meta { font-size: 13px; color: #111; line-height: 1.45; }
    .kanban-task .task-meta i { font-size: 12px !important; }
    .kanban-task .badge { font-size: 11px !important; }
    .task-count { background: rgba(255,255,255,0.3); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; }
    .task-cancel-reason { font-size: 12px; margin-top: 6px; padding: 6px; background: #fdeaea; border-radius: 4px; color: #c62828; }
</style>
@endpush
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Kanban Board</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Kanban</a></li>
        </ul>
    </div>

    <div class="mb-3">
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
            <label class="form-label mb-0 me-1">Employee</label>
            <select name="employee_id" class="form-select form-select-sm" style="width: 260px;" onchange="this.form.submit()">
                <option value="">All employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp->id }}" {{ (string)($employeeId ?? '') === (string)$emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                @endforeach
            </select>
        </form>
        <small class="text-muted">When an employee is selected, tasks show if they are the current assignee or were originally assigned.</small>
    </div>

    @php
        $columnColors = ['todo'=>'warning','in_progress'=>'primary','in_review'=>'info','completed'=>'success','on_hold'=>'secondary','cancelled'=>'danger'];
    @endphp

    <div class="kanban-board">
        @foreach($statuses as $status)
            <div class="kanban-column">
                <div class="card">
                    <div class="card-header bg-{{ $columnColors[$status] ?? 'secondary' }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ ucfirst(str_replace('_',' ',$status)) }}</strong>
                            <span class="task-count">{{ isset($tasks[$status]) ? $tasks[$status]->count() : 0 }}</span>
                        </div>
                    </div>
                    <div class="card-body p-2 kanban-drop" data-status="{{ $status }}" style="min-height: 350px;">
                        @foreach(($tasks[$status] ?? collect()) as $task)
                            <div class="kanban-task" draggable="true" data-task-id="{{ $task->id }}">
                                <div class="task-title d-flex justify-content-between align-items-start gap-1">
                                    <a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 32) }}</a>
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-link btn-sm p-0 text-secondary" title="Edit" onclick="event.stopPropagation();"><i class="fas fa-edit"></i></a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="badge bg-{{ ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'][$task->priority] ?? 'secondary' }}">{{ ucfirst($task->priority) }}</span>
                                </div>
                                <div class="task-meta mt-1">
                                    <div><i class="fas fa-user" style="font-size:10px;"></i> <strong>Now:</strong> {{ $task->assignee->name ?? 'Unassigned' }}</div>
                                    <div><i class="fas fa-user-clock" style="font-size:10px;"></i> <strong>Initially:</strong> {{ $task->originalAssignee?->name ?? '—' }}</div>
                                    @if($task->due_date)
                                        <div><i class="fas fa-calendar" style="font-size:10px;"></i> Due {{ $task->due_date->format('M d') }}</div>
                                    @endif
                                    <div><i class="fas fa-hourglass-half" style="font-size:10px;"></i> {{ $task->daysSinceCreation() }} {{ Str::plural('day', $task->daysSinceCreation()) }} since created</div>
                                </div>
                                @if($task->status === 'cancelled' && $task->cancellation_reason)
                                    <div class="task-cancel-reason kanban-cancel-display">{{ Str::limit($task->cancellation_reason, 120) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script>
    let draggedTask = null;
    document.querySelectorAll('.kanban-task').forEach(task => {
        task.addEventListener('dragstart', e => { draggedTask = task; task.style.opacity = '0.5'; });
        task.addEventListener('dragend', e => { task.style.opacity = '1'; });
    });

    function patchTaskStatus(taskId, newStatus, cancellationReason) {
        const body = { status: newStatus };
        if (cancellationReason) body.cancellation_reason = cancellationReason;
        return fetch('/admin/tasks/' + taskId + '/status', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            },
            body: JSON.stringify(body)
        }).then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) {
                let msg = data.message || 'Failed to update status';
                if (data.errors) msg = Object.values(data.errors).flat().join(' ');
                throw new Error(msg);
            }
            return data;
        });
    }

    document.querySelectorAll('.kanban-drop').forEach(col => {
        col.addEventListener('dragover', e => { e.preventDefault(); col.style.background = '#f0f6ff'; });
        col.addEventListener('dragleave', e => { col.style.background = ''; });
        col.addEventListener('drop', e => {
            e.preventDefault();
            col.style.background = '';
            if (!draggedTask) return;
            const sourceCol = draggedTask.closest('.kanban-drop');
            const taskId = draggedTask.dataset.taskId;
            const newStatus = col.dataset.status;
            if (sourceCol === col) return;

            const statusLabel = newStatus.replace(/_/g, ' ');
            swal({
                title: 'Move task?',
                text: 'Change status to "' + statusLabel + '"?',
                icon: 'info',
                buttons: ['Cancel', 'Confirm'],
                dangerMode: false,
            }).then(function(confirm) {
                if (!confirm) return;

                const moveDom = function(cancelText) {
                    col.appendChild(draggedTask);
                    let el = draggedTask.querySelector('.kanban-cancel-display');
                    if (newStatus === 'cancelled' && cancelText) {
                        if (!el) {
                            el = document.createElement('div');
                            el.className = 'task-cancel-reason kanban-cancel-display';
                            draggedTask.appendChild(el);
                        }
                        el.textContent = cancelText.length > 120 ? cancelText.slice(0, 117) + '…' : cancelText;
                    } else if (el && newStatus !== 'cancelled') {
                        el.remove();
                    }
                };

                const onSuccess = function(cancelText) {
                    moveDom(cancelText);
                    showToast('Task status updated', 'success');
                };

                const onFail = function(err) {
                    showToast(err.message || 'Failed', 'error');
                };

                if (newStatus === 'cancelled') {
                    const reason = window.prompt('Please enter the reason for cancellation:');
                    if (reason === null) return;
                    const trimmed = (reason || '').trim();
                    if (!trimmed) {
                        showToast('Cancellation reason is required.', 'error');
                        return;
                    }
                    patchTaskStatus(taskId, newStatus, trimmed).then(function() {
                        onSuccess(trimmed);
                    }).catch(function(err) { onFail(err); });
                } else {
                    patchTaskStatus(taskId, newStatus, null).then(function() {
                        onSuccess(null);
                    }).catch(function(err) { onFail(err); });
                }
            });
        });
    });
</script>
@endpush
