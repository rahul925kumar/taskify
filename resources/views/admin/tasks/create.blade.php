@extends('layouts.app')
@section('title', 'Create Task')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Create Task</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.tasks.index') }}">Tasks</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Create</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="card-title">New Task</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.tasks.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Assign To <span class="text-danger">*</span></label>
                        <select name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ old('assigned_to') == $emp->id ? 'selected' : '' }}>{{ $emp->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', date('Y-m-d')) }}" required>
                        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Due in (days) <span class="text-danger">*</span></label>
                        <input type="number" name="due_days" class="form-control @error('due_days') is-invalid @enderror" value="{{ old('due_days', 7) }}" min="1" max="3650" required>
                        <small class="text-muted">Due date = start date + this many days</small>
                        @error('due_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            @foreach(config('constants.task_statuses') as $s)
                                <option value="{{ $s }}" {{ old('status', 'todo') == $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select" required>
                            @foreach(config('constants.task_priorities') as $p)
                                <option value="{{ $p }}" {{ old('priority', 'medium') == $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Attachments</label>
                    <input type="file" name="attachments[]" class="form-control" multiple>
                    <small class="text-muted">You can upload multiple files (max 10MB each).</small>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Create Task</button>
                    <a href="{{ route('admin.tasks.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
