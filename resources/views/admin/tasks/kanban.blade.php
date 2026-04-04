@extends('layouts.app')
@section('title', 'Kanban Board')
@push('styles')
<style>
    .kanban-board { display: flex; gap: 15px; overflow-x: auto; padding-bottom: 20px; }
    .kanban-column { min-width: 280px; max-width: 300px; flex-shrink: 0; }
    .kanban-column .card { min-height: 400px; }
    .kanban-column .card-header { padding: 10px 15px; }
    .kanban-task { background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 12px; margin-bottom: 10px; cursor: grab; transition: box-shadow 0.2s; }
    .kanban-task:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .kanban-task .task-title { font-weight: 600; font-size: 14px; margin-bottom: 5px; }
    .kanban-task .task-meta { font-size: 11px; color: #8d9498; }
    .task-count { background: rgba(255,255,255,0.3); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; }
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
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="project_id" class="form-select form-select-sm" style="width: 250px;" onchange="this.form.submit()">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ $projectId == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </form>
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
                                <div class="task-title">
                                    <a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 35) }}</a>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="badge bg-{{ ['low'=>'secondary','medium'=>'info','high'=>'warning','urgent'=>'danger'][$task->priority] ?? 'secondary' }}" style="font-size:10px;">{{ ucfirst($task->priority) }}</span>
                                    <span class="task-meta">{{ $task->project->name ?? '' }}</span>
                                </div>
                                <div class="task-meta mt-1">
                                    <i class="fas fa-user" style="font-size:10px;"></i> {{ $task->assignee->name ?? 'Unassigned' }}
                                    @if($task->due_date)
                                        &middot; <i class="fas fa-calendar" style="font-size:10px;"></i> {{ $task->due_date->format('M d') }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
<script>
    let draggedTask = null;
    document.querySelectorAll('.kanban-task').forEach(task => {
        task.addEventListener('dragstart', e => { draggedTask = task; task.style.opacity = '0.5'; });
        task.addEventListener('dragend', e => { task.style.opacity = '1'; });
    });

    document.querySelectorAll('.kanban-drop').forEach(col => {
        col.addEventListener('dragover', e => { e.preventDefault(); col.style.background = '#f0f6ff'; });
        col.addEventListener('dragleave', e => { col.style.background = ''; });
        col.addEventListener('drop', e => {
            e.preventDefault();
            col.style.background = '';
            if (draggedTask) {
                col.appendChild(draggedTask);
                let taskId = draggedTask.dataset.taskId;
                let newStatus = col.dataset.status;
                fetch(`/admin/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({ status: newStatus })
                }).then(r => {
                    if (r.ok) showToast('Task status updated to ' + newStatus.replaceAll('_', ' '), 'success');
                    else r.json().then(d => showToast(d.message || 'Failed to update status', 'error')).catch(() => showToast('Failed to update status', 'error'));
                }).catch(() => showToast('Network error', 'error'));
            }
        });
    });
</script>
@endpush
