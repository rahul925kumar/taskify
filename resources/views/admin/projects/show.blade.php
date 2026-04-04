@extends('layouts.app')

@section('title', $project->name)

@section('content')
    @php
        $projectStatusClass = match ($project->status) {
            'not_started' => 'secondary',
            'in_progress' => 'primary',
            'on_hold' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    @endphp

    <div class="page-header">
        <h3 class="fw-bold mb-3">{{ $project->name }}</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">{{ Str::limit($project->name, 50) }}</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12 col-lg-5 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="card-title mb-0">Overview</div>
                    <div class="d-flex flex-wrap gap-1">
                        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-warning btn-sm"><i class="fa fa-edit me-1"></i> Edit</a>
                        <a href="{{ route('admin.tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm"><i class="fa fa-plus me-1"></i> New task</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Status</strong></p>
                    <p class="mb-3"><span class="badge bg-{{ $projectStatusClass }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span></p>

                    <p class="mb-2"><strong>Start date</strong></p>
                    <p class="text-muted mb-3">{{ $project->start_date?->format('M j, Y') ?? '—' }}</p>

                    <p class="mb-2"><strong>End date</strong></p>
                    <p class="text-muted mb-3">{{ $project->end_date?->format('M j, Y') ?? '—' }}</p>

                    <p class="mb-2"><strong>Client</strong></p>
                    @if($project->client)
                        <p class="mb-1">{{ $project->client->name }}</p>
                        @if($project->client->email)
                            <p class="text-muted small mb-1"><i class="fa fa-envelope me-1"></i>{{ $project->client->email }}</p>
                        @endif
                        @if($project->client->phone)
                            <p class="text-muted small mb-0"><i class="fa fa-phone me-1"></i>{{ $project->client->phone }}</p>
                        @endif
                    @else
                        <p class="text-muted mb-0">—</p>
                    @endif

                    @if($project->description)
                        <hr>
                        <p class="mb-2"><strong>Description</strong></p>
                        <div class="text-muted" style="white-space: pre-wrap;">{{ $project->description }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-12 col-lg-7">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="card-title mb-0">Tasks</div>
                    <a href="{{ route('admin.tasks.create', ['project_id' => $project->id]) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> Add task
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Assignee</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($project->tasks as $task)
                                    @php
                                        $tsClass = match ($task->status) {
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'in_progress' => 'primary',
                                            'in_review' => 'info',
                                            'on_hold' => 'warning',
                                            default => 'secondary',
                                        };
                                        $prClass = match ($task->priority) {
                                            'urgent' => 'danger',
                                            'high' => 'warning',
                                            'medium' => 'info',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.tasks.show', $task) }}">{{ Str::limit($task->title, 48) }}</a>
                                        </td>
                                        <td>{{ $task->assignee?->name ?? 'Unassigned' }}</td>
                                        <td><span class="badge bg-{{ $tsClass }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span></td>
                                        <td><span class="badge bg-{{ $prClass }}">{{ ucfirst($task->priority) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No tasks for this project yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
