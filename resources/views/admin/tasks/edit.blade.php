@extends('layouts.app')
@section('title', 'Edit Task')
@section('content')
    @php
        $dueDaysValue = old('due_days');
        if ($dueDaysValue === null && $task->start_date && $task->due_date) {
            $dueDaysValue = max(1, $task->start_date->diffInDays($task->due_date, false));
        }
        $dueDaysValue = $dueDaysValue ?? 7;
    @endphp
    <div class="page-header">
        <h3 class="fw-bold mb-3">Edit Task</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.tasks.index') }}">Tasks</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Edit</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="card-title">Edit: {{ $task->title }}</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tasks.update', $task) }}">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $task->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $task->description) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Assign to <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select" required>
                            @foreach($assignableUsers as $u)
                                <option value="{{ $u->id }}" {{ (string) old('assigned_to', $task->assigned_to) === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}{{ $u->is_admin ? ' (Admin)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Due in (days) <span class="text-danger">*</span></label>
                        <input type="number" name="due_days" class="form-control" value="{{ $dueDaysValue }}" min="1" max="3650" required>
                        <small class="text-muted">Recalculates due date from start date</small>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(config('constants.task_statuses') as $s)
                                <option value="{{ $s }}" {{ old('status', $task->status) == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select" required>
                            @foreach(config('constants.task_priorities') as $p)
                                <option value="{{ $p }}" {{ old('priority', $task->priority) == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Update Task</button>
                    <a href="{{ route('admin.tasks.show', $task) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
