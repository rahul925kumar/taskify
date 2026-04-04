@extends('layouts.app')
@section('title', 'Edit Task')
@section('content')
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
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $task->title) }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Project <span class="text-danger">*</span></label>
                        <select name="project_id" class="form-select" required>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $task->description) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Assign To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('assigned_to', $task->assigned_to) == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $task->start_date?->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
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
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            @foreach(config('constants.task_types') as $t)
                                <option value="{{ $t }}" {{ old('type', $task->type) == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select" required>
                            @foreach(config('constants.task_categories') as $c)
                                <option value="{{ $c }}" {{ old('category', $task->category) == $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
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
