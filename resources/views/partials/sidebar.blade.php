<div class="sidebar-area" id="sidebar-area">
    <div class="logo position-relative">
        <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ setting_image('main_logo') ?? url('/assets/images/favicon.png') }}" alt="Logo" class="wh-40 rounded-circle">
            <span class="logo-text fw-bold ms-2 text-white">SBRS Admin</span>
        </a>
        <button class="sidebar-burger-menu bg-transparent p-0 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y" id="sidebar-burger-menu">
            <i class="material-symbols-outlined">close</i>
        </button>
    </div>

    <aside id="layout-menu" class="layout-menu menu-vertical menu" data-simplebar>
        <ul class="menu-inner">
            {{-- Dashboard --}}
            @can('dashboard.view')
            <li class="menu-item {{ request()->routeIs('admin.dashboard') ? 'open' : '' }}">
                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">dashboard</i>
                    <span class="title">Dashboard</span>
                </a>
            </li>
            @endcan

            {{-- Academic Management --}}
            @canany(['academic-sessions.view', 'programmes.view', 'courses.view'])
            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">ACADEMIC</span>
            </li>
            @endcanany
            @can('academic-sessions.view')
            <li class="menu-item {{ request()->routeIs('admin.academic-sessions.*') ? 'open' : '' }}">
                <a href="{{ route('admin.academic-sessions.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">calendar_month</i>
                    <span class="title">Academic Sessions</span>
                </a>
            </li>
            @endcan
            @can('programmes.view')
            <li class="menu-item {{ request()->routeIs('admin.programmes.*') ? 'open' : '' }}">
                <a href="{{ route('admin.programmes.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">school</i>
                    <span class="title">Programmes</span>
                </a>
            </li>
            @endcan
            @can('courses.view')
            <li class="menu-item {{ request()->routeIs('admin.courses.*') ? 'open' : '' }}">
                <a href="{{ route('admin.courses.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">menu_book</i>
                    <span class="title">Courses</span>
                </a>
            </li>
            @endcan

            {{-- Fee Management --}}
            @canany(['fees.view', 'payments.view'])
            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">FINANCE</span>
            </li>
            @endcanany
            @can('fees.view')
            <li class="menu-item {{ request()->routeIs('admin.fees.*') ? 'open' : '' }}">
                <a href="{{ route('admin.fees.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">payments</i>
                    <span class="title">Fee Management</span>
                </a>
            </li>
            @endcan
            @can('payments.view')
            <li class="menu-item {{ request()->routeIs('admin.payments.*') ? 'open' : '' }}">
                <a href="{{ route('admin.payments.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">receipt_long</i>
                    <span class="title">Payment Records</span>
                </a>
            </li>
            @endcan

            {{-- Admissions --}}
            @canany(['applications.view', 'screening.view'])
            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">ADMISSIONS</span>
            </li>
            @endcanany
            @can('applications.view')
            <li class="menu-item {{ request()->routeIs('admin.registered-users.*') ? 'open' : '' }}">
                <a href="{{ route('admin.registered-users.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">people</i>
                    <span class="title">Registered Users</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.applications.*') ? 'open' : '' }}">
                <a href="{{ route('admin.applications.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">description</i>
                    <span class="title">Applications</span>
                </a>
            </li>
            @endcan
            @can('screening.view')
            <li class="menu-item {{ request()->routeIs('admin.screening.*') ? 'open' : '' }}">
                <a href="{{ route('admin.screening.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">fact_check</i>
                    <span class="title">Screening</span>
                </a>
            </li>
            @endcan

            {{-- Students --}}
            @canany(['students.view', 'results.view'])
            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">STUDENTS</span>
            </li>
            @endcanany
            @can('students.view')
            <li class="menu-item {{ request()->routeIs('admin.students.*') ? 'open' : '' }}">
                <a href="{{ route('admin.students.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">groups</i>
                    <span class="title">Student Records</span>
                </a>
            </li>
            @endcan
            @can('results.view')
            <li class="menu-item {{ request()->routeIs('admin.results.*') ? 'open' : '' }}">
                <a href="{{ route('admin.results.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">assessment</i>
                    <span class="title">Results</span>
                </a>
            </li>
            @endcan

            {{-- System --}}
            @canany(['users.view', 'roles.view', 'settings.view', 'audit-logs.view'])
            <li class="menu-title small text-uppercase">
                <span class="menu-title-text">SYSTEM</span>
            </li>
            @endcanany
            @can('users.view')
            <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'open' : '' }}">
                <a href="{{ route('admin.users.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">person</i>
                    <span class="title">Users</span>
                </a>
            </li>
            <li class="menu-item {{ request()->routeIs('admin.password-reset.*') ? 'open' : '' }}">
                <a href="{{ route('admin.password-reset.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">lock_reset</i>
                    <span class="title">Password Reset</span>
                </a>
            </li>
            @endcan
            @can('roles.view')
            <li class="menu-item {{ request()->routeIs('admin.roles.*') ? 'open' : '' }}">
                <a href="{{ route('admin.roles.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">admin_panel_settings</i>
                    <span class="title">Roles & Permissions</span>
                </a>
            </li>
            @endcan
            @can('settings.view')
            <li class="menu-item {{ request()->routeIs('admin.settings.*') ? 'open' : '' }}">
                <a href="{{ route('admin.settings.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">settings</i>
                    <span class="title">Settings</span>
                </a>
            </li>
            @endcan
            @can('audit-logs.view')
            <li class="menu-item {{ request()->routeIs('admin.audit-logs.*') ? 'open' : '' }}">
                <a href="{{ route('admin.audit-logs.index') }}" class="menu-link">
                    <i class="material-symbols-outlined menu-icon">history</i>
                    <span class="title">Audit Logs</span>
                </a>
            </li>
            @endcan
        </ul>
    </aside>
</div>
