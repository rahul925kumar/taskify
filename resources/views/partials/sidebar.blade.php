<div class="sidebar" data-background-color="dark">
    <div class="sidebar-logo">
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
    <div class="sidebar-wrapper scrollbar scrollbar-inner">
        <div class="sidebar-content">
            <ul class="nav nav-secondary">
                @if(auth()->user()->is_admin)
                    {{-- Admin Sidebar --}}
                    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Management</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.employees.index') }}">
                            <i class="fas fa-users"></i>
                            <p>Employees</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.clients.index') }}">
                            <i class="fas fa-user-tie"></i>
                            <p>Clients</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.projects.index') }}">
                            <i class="fas fa-project-diagram"></i>
                            <p>Projects</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Tasks</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.tasks.index') }}">
                            <i class="fas fa-tasks"></i>
                            <p>All Tasks</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.tasks.kanban') ? 'active' : '' }}">
                        <a href="{{ route('admin.tasks.kanban') }}">
                            <i class="fas fa-columns"></i>
                            <p>Kanban Board</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Reports</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.reports.index') }}">
                            <i class="fas fa-chart-bar"></i>
                            <p>Task Reports</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.leaves.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.leaves.index') }}">
                            <i class="fas fa-calendar-minus"></i>
                            <p>Leave Management</p>
                            @php $pendingLeaves = \App\Models\Leave::where('status', 'pending')->count(); @endphp
                            @if($pendingLeaves > 0)
                                <span class="badge badge-count bg-danger">{{ $pendingLeaves }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.attendance.index') }}">
                            <i class="fas fa-clock"></i>
                            <p>Attendance</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            <p>Notifications</p>
                        </a>
                    </li>
                @else
                    {{-- Employee Sidebar --}}
                    <li class="nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('employee.dashboard') }}">
                            <i class="fas fa-home"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">My Work</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('employee.tasks.index') ? 'active' : '' }}">
                        <a href="{{ route('employee.tasks.index') }}">
                            <i class="fas fa-tasks"></i>
                            <p>My Tasks</p>
                        </a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('employee.tasks.kanban') ? 'active' : '' }}">
                        <a href="{{ route('employee.tasks.kanban') }}">
                            <i class="fas fa-columns"></i>
                            <p>Kanban Board</p>
                        </a>
                    </li>
                    <li class="nav-section">
                        <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                        <h4 class="text-section">Leave</h4>
                    </li>
                    <li class="nav-item {{ request()->routeIs('employee.leaves.*') ? 'active' : '' }}">
                        <a href="{{ route('employee.leaves.index') }}">
                            <i class="fas fa-calendar-minus"></i>
                            <p>My Leaves</p>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
