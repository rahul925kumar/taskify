@extends('layouts.app')
@section('title', 'New Petty Cash Entry')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">New petty cash entry</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.petty-cash.index') }}">Petty Cash</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Create</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.petty-cash.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Transaction date &amp; time <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="transaction_at" class="form-control @error('transaction_at') is-invalid @enderror"
                               value="{{ old('transaction_at', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('transaction_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="credit" {{ old('type') === 'credit' ? 'selected' : '' }}>Received (money in)</option>
                            <option value="debit" {{ old('type', 'debit') === 'debit' ? 'selected' : '' }}>Paid out (expense)</option>
                        </select>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" step="0.01" min="0.01" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference / voucher no.</label>
                    <input type="text" name="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference') }}" maxlength="191" placeholder="Optional">
                    @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Optional notes">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <button type="submit" class="btn btn-primary">Save entry</button>
                <a href="{{ route('admin.petty-cash.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
