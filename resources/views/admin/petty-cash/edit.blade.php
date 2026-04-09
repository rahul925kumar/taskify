@extends('layouts.app')
@section('title', 'Edit Petty Cash')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Edit petty cash entry</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.petty-cash.index') }}">Petty Cash</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Edit</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.petty-cash.update', $transaction->id) }}">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Transaction date &amp; time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="transaction_at" class="form-control @error('transaction_at') is-invalid @enderror"
                               value="{{ old('transaction_at', $transaction->transaction_at->format('Y-m-d\TH:i')) }}" required>
                        @error('transaction_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="credit" {{ old('type', $transaction->type) === 'credit' ? 'selected' : '' }}>Received (money in)</option>
                            <option value="debit" {{ old('type', $transaction->type) === 'debit' ? 'selected' : '' }}>Paid out (expense)</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $transaction->amount) }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference / voucher no.</label>
                    <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $transaction->reference) }}" maxlength="191">
                    @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $transaction->description) }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <p class="text-muted small">Originally recorded by {{ $transaction->recorder?->name ?? '—' }} on {{ $transaction->created_at->format('M d, Y h:i A') }}</p>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.petty-cash.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
