<div class="main-header">
    <div class="main-header-logo">
        <div class="logo-header" data-background-color="dark">
            <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('employee.dashboard') }}" class="logo">
                <img src="{{ asset('assets/img/kaiadmin/logo_custom.webp') }}" alt="Task Manager" class="navbar-brand" height="30" />
            </a>
            <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
            </div>
            <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
        </div>
    </div>
    <nav class="navbar navbar-header navbar-header-left navbar-expand-lg navbar-form">
        <div class="col-nav-header ms-md-auto">
            <ul class="nav nav-line">
                <li class="nav-item d-flex align-items-center">
                    <span class="badge bg-{{ auth()->user()->is_admin ? 'primary' : 'success' }} me-2">
                        {{ auth()->user()->is_admin ? 'Admin' : auth()->user()->role }}
                    </span>
                </li>
            </ul>
        </div>
    </nav>
    <nav class="navbar navbar-header navbar-header-right navbar-expand-lg navbar-form">
        <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
            @php
                $unreadCount = auth()->user()->unreadNotifications()->count();
            @endphp
            <li class="nav-item topbar-icon dropdown hidden-caret">
                <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown">
                    <i class="fa fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="notification">{{ $unreadCount }}</span>
                    @endif
                </a>
                <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
                    <li>
                        <div class="dropdown-title">You have {{ $unreadCount }} new notifications</div>
                    </li>
                    <li>
                        <div class="notif-scroll scrollbar-outer">
                            <div class="notif-center">
                                @foreach(auth()->user()->unreadNotifications()->latest()->take(5)->get() as $notif)
                                    <a href="{{ $notif->data['url'] ?? '#' }}">
                                        <div class="notif-icon notif-primary"><i class="fa fa-bell"></i></div>
                                        <div class="notif-content">
                                            <span class="block">{{ $notif->data['message'] ?? 'New notification' }}</span>
                                            <span class="time">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </li>
                </ul>
            </li>
            <li class="nav-item topbar-user dropdown hidden-caret">
                <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#">
                    <div class="avatar-sm">
                        <img src="{{ auth()->user()->image ? asset('uploads/employees/' . auth()->user()->image) : asset('assets/img/kaiadmin/favicon.ico') }}" alt="..." class="avatar-img rounded-circle" />
                    </div>
                    <span class="profile-username">
                        <span class="op-7">Hi,</span>
                        <span class="fw-bold">{{ auth()->user()->name }}</span>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                        <li>
                            <div class="user-box">
                                <div class="avatar-lg">
                                    <img src="{{ auth()->user()->image ? asset('uploads/employees/' . auth()->user()->image) : asset('assets/img/kaiadmin/favicon.ico') }}" alt="image profile" class="avatar-img rounded" />
                                </div>
                                <div class="u-text">
                                    <h4>{{ auth()->user()->name }}</h4>
                                    <p class="text-muted">{{ auth()->user()->email }}</p>
                                </div>
                            </div>
                        </li>
                        <li>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i> Logout
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                        </li>
                    </div>
                </ul>
            </li>
        </ul>
    </nav>
</div>
