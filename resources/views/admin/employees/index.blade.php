@extends('layouts.app')
@section('title', 'Employees')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Employees</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="card-title mb-0">All Employees</div>
            <a href="{{ route('admin.employees.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> New Employee
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 56px;">Photo</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Done</th>
                            <th class="text-center">Pending</th>
                            <th class="text-end" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td>
                                    <div class="avatar avatar-sm">
                                        <img src="{{ $employee->image ? asset('uploads/employees/' . $employee->image) : asset('assets/img/kaiadmin/favicon.ico') }}"
                                             alt=""
                                             class="avatar-img rounded-circle"
                                             style="object-fit: cover;">
                                    </div>
                                </td>
                                <td><strong>{{ $employee->name }}</strong></td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->phone ?? '—' }}</td>
                                <td>{{ $employee->role }}</td>
                                <td class="text-center">{{ $employee->total_tasks }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $employee->completed_tasks }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $employee->pending_tasks }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('admin.employees.show', $employee) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('admin.employees.destroy', $employee) }}"
                                          class="d-inline delete-employee-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($employees->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $employees->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script>
        document.querySelectorAll('.delete-employee-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                swal({
                    title: 'Delete this employee?',
                    text: 'This will remove the employee record. You can confirm to continue.',
                    icon: 'warning',
                    buttons: ['Cancel', 'Delete'],
                    dangerMode: true,
                }).then(function (willDelete) {
                    if (willDelete) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
