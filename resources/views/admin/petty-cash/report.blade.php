@extends('layouts.app')
@section('title', 'Petty Cash Report')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Petty cash report</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.petty-cash.index') }}">Petty Cash</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Report</a></li>
        </ul>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h4 class="card-title mb-0">Select date &amp; time range</h4></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.petty-cash.report') }}" class="row g-3 align-items-end">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">From</label>
                    <input type="datetime-local" name="from" class="form-control" value="{{ $fromInput }}" required>
                </div>
                <div class="col-md-4 col-lg-3">
                    <label class="form-label">To</label>
                    <input type="datetime-local" name="to" class="form-control" value="{{ $toInput }}" required>
                </div>
                <div class="col-md-4 col-lg-4">
                    <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search me-1"></i> Generate report</button>
                    <a href="{{ route('admin.petty-cash.index') }}" class="btn btn-outline-secondary">Back to ledger</a>
                </div>
            </form>
            <p class="text-muted small mb-0 mt-2">Range applied: <strong>{{ $from->format('M d, Y h:i A') }}</strong> — <strong>{{ $to->format('M d, Y h:i A') }}</strong> (app timezone)</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body py-3">
                <p class="small text-muted mb-1">Opening balance</p>
                <h5 class="mb-0 {{ $openingBalance >= 0 ? 'text-primary' : 'text-warning' }}">₹{{ number_format($openingBalance, 2) }}</h5>
                <small class="text-muted">Before range start</small>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body py-3">
                <p class="small text-muted mb-1">Received in period</p>
                <h5 class="mb-0 text-success">₹{{ number_format($periodCredit, 2) }}</h5>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body py-3">
                <p class="small text-muted mb-1">Paid out in period</p>
                <h5 class="mb-0 text-danger">₹{{ number_format($periodDebit, 2) }}</h5>
            </div></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card"><div class="card-body py-3">
                <p class="small text-muted mb-1">Closing balance</p>
                <h5 class="mb-0 {{ $closingBalance >= 0 ? 'text-primary' : 'text-warning' }}">₹{{ number_format($closingBalance, 2) }}</h5>
                <small class="text-muted">End of range</small>
            </div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="card-title mb-0">Transactions in range ({{ $count }})</span>
            <a href="{{ route('admin.petty-cash.report.csv', ['from' => $fromInput, 'to' => $toInput]) }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-file-csv me-1"></i> Download CSV
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Running balance</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Recorded by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            @php $t = $row->transaction; @endphp
                            <tr>
                                <td>{{ $t->transaction_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if($t->type === 'credit')
                                        <span class="badge bg-success">Received</span>
                                    @else
                                        <span class="badge bg-danger">Paid out</span>
                                    @endif
                                </td>
                                <td class="text-end">{{ $t->type === 'credit' ? '+' : '−' }}₹{{ number_format($t->amount, 2) }}</td>
                                <td class="text-end fw-semibold">₹{{ number_format($row->running_balance, 2) }}</td>
                                <td>{{ $t->description ?? '—' }}</td>
                                <td>{{ $t->reference ?? '—' }}</td>
                                <td>{{ $t->recorder?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No transactions in this range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
