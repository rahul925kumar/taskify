@extends('layouts.app')
@section('title', 'Petty Cash')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Petty Cash</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Petty Cash</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <p class="card-category text-muted mb-1">Total received (all time)</p>
                    <h4 class="card-title text-success mb-0">₹{{ number_format($totalCredit, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <p class="card-category text-muted mb-1">Total spent (all time)</p>
                    <h4 class="card-title text-danger mb-0">₹{{ number_format($totalDebit, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <p class="card-category text-muted mb-1">Current balance</p>
                    <h4 class="card-title {{ $balance >= 0 ? 'text-primary' : 'text-warning' }} mb-0">₹{{ number_format($balance, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <div class="card-title mb-0">Transactions</div>
            <div class="ms-md-auto d-flex flex-wrap gap-2">
                <a href="{{ route('admin.petty-cash.report') }}" class="btn btn-info btn-sm"><i class="fas fa-file-alt me-1"></i> Date range report</a>
                <a href="{{ route('admin.petty-cash.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> New entry</a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small mb-0">From (date &amp; time)</label>
                    <input type="datetime-local" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label small mb-0">To (date &amp; time)</label>
                    <input type="datetime-local" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-md-4 col-lg-3">
                    <button type="submit" class="btn btn-sm btn-secondary">Filter list</button>
                    <a href="{{ route('admin.petty-cash.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Recorded by</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $t)
                            <tr>
                                <td>{{ $t->transaction_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if($t->type === 'credit')
                                        <span class="badge bg-success">Received</span>
                                    @else
                                        <span class="badge bg-danger">Paid out</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">₹{{ number_format($t->amount, 2) }}</td>
                                <td>{{ Str::limit($t->description ?? '—', 50) }}</td>
                                <td>{{ $t->reference ?? '—' }}</td>
                                <td>{{ $t->recorder?->name ?? '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.petty-cash.edit', $t->id) }}" class="btn btn-label-info btn-sm"><i class="fas fa-edit"></i></a>
                                    <form id="del-pc-{{ $t->id }}" method="POST" action="{{ route('admin.petty-cash.destroy', $t->id) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-label-danger btn-sm btn-del-pc" data-form="del-pc-{{ $t->id }}"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No petty cash entries yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
<script>
    document.querySelectorAll('.btn-del-pc').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-form');
            swal({ title: 'Delete this entry?', icon: 'warning', buttons: true, dangerMode: true }).then(function (ok) {
                if (ok) document.getElementById(id).submit();
            });
        });
    });
</script>
@endpush
