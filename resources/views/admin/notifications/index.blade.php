@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
    <div class="page-header">
        <h3 class="fw-bold mb-3">Notifications</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Notifications</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-title">All Notifications</h4>
                <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-info">Mark All as Read</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @forelse($notifications as $notif)
                <div class="d-flex justify-content-between align-items-start p-3 mb-2 rounded {{ $notif->read_at ? 'bg-light' : 'bg-white border' }}">
                    <div>
                        <i class="fas fa-bell me-2 {{ $notif->read_at ? 'text-muted' : 'text-primary' }}"></i>
                        <strong>{{ $notif->data['title'] ?? 'Notification' }}</strong>
                        <p class="mb-0 text-muted">{{ $notif->data['message'] ?? '' }}</p>
                        <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                    </div>
                    <div>
                        @if(!$notif->read_at)
                            <form method="POST" action="{{ route('admin.notifications.read', $notif->id) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-xs btn-outline-primary">Mark Read</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted text-center">No notifications.</p>
            @endforelse
            {{ $notifications->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
