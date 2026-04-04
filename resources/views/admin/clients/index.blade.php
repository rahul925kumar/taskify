@extends('layouts.app')
@section('title', 'Clients')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Clients</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.clients.index') }}">Clients</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center flex-wrap gap-2">
            <div class="card-title mb-0">All clients</div>
            <a href="{{ route('admin.clients.create') }}" class="btn btn-primary btn-sm ms-md-auto">
                <i class="fas fa-plus me-1"></i> Create client
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Company</th>
                            <th scope="col" class="text-center">Projects</th>
                            <th scope="col" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clients as $client)
                            <tr>
                                <td>{{ $client->name }}</td>
                                <td>{{ $client->email ?? '—' }}</td>
                                <td>{{ $client->phone ?? '—' }}</td>
                                <td>{{ $client->company ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $client->projects_count ?? 0 }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-label-info btn-sm me-1" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                        class="btn btn-label-danger btn-sm btn-delete-client"
                                        title="Delete"
                                        data-action="{{ route('admin.clients.destroy', $client) }}"
                                        data-name="{{ $client->name }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No clients found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $clients->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <form id="delete-client-form" method="POST" action="" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script>
        document.querySelectorAll('.btn-delete-client').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = this.getAttribute('data-action');
                var name = this.getAttribute('data-name') || 'this client';
                swal({
                    title: 'Delete client?',
                    text: 'Are you sure you want to delete "' + name + '"? This cannot be undone.',
                    type: 'warning',
                    buttons: {
                        cancel: {
                            visible: true,
                            text: 'Cancel',
                            className: 'btn btn-light',
                        },
                        confirm: {
                            text: 'Yes, delete',
                            className: 'btn btn-danger',
                        },
                    },
                }).then(function (willDelete) {
                    if (willDelete) {
                        var form = document.getElementById('delete-client-form');
                        form.setAttribute('action', action);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
