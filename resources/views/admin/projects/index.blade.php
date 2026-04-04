@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Projects</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.projects.index') }}">Projects</a></li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="card-title mb-0">All projects</div>
                    <a href="{{ route('admin.projects.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus me-1"></i> New project
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Client</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th class="text-center">Tasks</th>
                                    <th class="text-end" style="width: 160px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                    @php
                                        $statusClass = match ($project->status) {
                                            'not_started' => 'secondary',
                                            'in_progress' => 'primary',
                                            'on_hold' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.projects.show', $project) }}" class="fw-semibold">{{ $project->name }}</a>
                                        </td>
                                        <td>{{ $project->client?->name ?? '—' }}</td>
                                        <td>{{ $project->start_date?->format('M j, Y') ?? '—' }}</td>
                                        <td>{{ $project->end_date?->format('M j, Y') ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $project->status)) }}</span>
                                        </td>
                                        <td class="text-center">{{ $project->tasks_count }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form id="delete-project-{{ $project->id }}" method="POST" action="{{ route('admin.projects.destroy', $project) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-project" title="Delete" data-form="delete-project-{{ $project->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No projects found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($projects->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $projects->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script>
        document.querySelectorAll('.btn-delete-project').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var formId = btn.getAttribute('data-form');
                swal({
                    title: 'Are you sure?',
                    text: 'This project will be deleted.',
                    type: 'warning',
                    buttons: {
                        cancel: {
                            visible: true,
                            text: 'Cancel',
                            className: 'btn btn-secondary',
                        },
                        confirm: {
                            text: 'Yes, delete',
                            className: 'btn btn-danger',
                        },
                    },
                }).then(function (confirmed) {
                    if (confirmed) {
                        document.getElementById(formId).submit();
                    }
                });
            });
        });
    </script>
@endpush
