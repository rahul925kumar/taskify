@extends('layouts.app')

@section('title', 'Edit project')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Edit project</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.projects.show', $project) }}">{{ Str::limit($project->name, 40) }}</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Edit</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Project details</div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.projects.update', $project) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Optional">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start date</label>
                                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" class="form-control @error('start_date') is-invalid @enderror">
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date">End date</label>
                                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}" class="form-control @error('end_date') is-invalid @enderror">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach(['not_started', 'in_progress', 'on_hold', 'completed', 'cancelled'] as $st)
                                    <option value="{{ $st }}" @selected(old('status', $project->status) === $st)>{{ ucfirst(str_replace('_', ' ', $st)) }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="client_id">Client</label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror">
                                <option value="">— None —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" @selected((string) old('client_id', $project->client_id) === (string) $client->id)>{{ $client->name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Update project</button>
                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
