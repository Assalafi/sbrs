@php
    $student = Auth::guard('student')->user();
    $logo = setting_image('main_logo') ?? asset('assets/images/favicon.png');
@endphp

<div class="student-sidebar-backdrop" id="student-sidebar-backdrop" aria-hidden="true"></div>
<aside class="student-sidebar" id="student-sidebar" aria-label="Student navigation">
    <div class="student-sidebar-top">
        <a class="student-sidebar-brand" href="{{ route('student.dashboard') }}">
            <span class="student-sidebar-logo-wrap"><img src="{{ $logo }}" alt="{{ setting('site_name', 'SBRS Portal') }}"></span>
            <span class="student-sidebar-brand-copy">
                <strong>{{ setting('site_name', 'SBRS Portal') }}</strong>
                <small>Student portal</small>
            </span>
        </a>
        <button type="button" class="student-sidebar-close" id="sidebar-burger-menu" aria-label="Close navigation">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>

    <div class="student-sidebar-profile">
        <div class="student-sidebar-avatar">
            @if($student?->passport_photo)
                <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="">
            @else
                {{ strtoupper(substr($student?->first_name ?? 'S', 0, 1) . substr($student?->surname ?? '', 0, 1)) }}
            @endif
        </div>
        <div class="student-sidebar-profile-copy">
            <strong>{{ $student?->first_name ?? 'Student' }}</strong>
            <span>{{ $student?->registration_number ?? 'Student account' }}</span>
        </div>
    </div>

    <nav class="student-sidebar-nav">
        <p class="student-nav-label">Overview</p>
        <a href="{{ route('student.dashboard') }}" class="student-nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
            <span class="material-symbols-outlined">space_dashboard</span><span>Dashboard</span>
        </a>

        <p class="student-nav-label">My academics</p>
        <a href="{{ route('student.courses.index') }}" class="student-nav-link {{ request()->routeIs('student.courses.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">menu_book</span><span>Course registration</span>
        </a>
        <a href="{{ route('student.results.index') }}" class="student-nav-link {{ request()->routeIs('student.results.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">grade</span><span>Results</span>
        </a>
        <a href="{{ route('student.registration.index') }}" class="student-nav-link {{ request()->routeIs('student.registration.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">how_to_reg</span><span>Registration</span>
        </a>

        <p class="student-nav-label">Services</p>
        <a href="{{ route('student.payments.index') }}" class="student-nav-link {{ request()->routeIs('student.payments.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">payments</span><span>Payments</span>
        </a>
        <a href="{{ route('student.hostel.index') }}" class="student-nav-link {{ request()->routeIs('student.hostel.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">bed</span><span>Hostel Application</span>
        </a>
        <a href="{{ route('student.biodata.index') }}" class="student-nav-link {{ request()->routeIs('student.biodata.*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">badge</span><span>My bio-data</span>
        </a>

        <p class="student-nav-label">Account</p>
        <a href="{{ route('student.password') }}" class="student-nav-link {{ request()->routeIs('student.password*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">lock</span><span>Change password</span>
        </a>
        <a href="#" class="student-nav-link student-nav-logout" onclick="event.preventDefault(); document.getElementById('student-sidebar-logout').submit();">
            <span class="material-symbols-outlined">logout</span><span>Sign out</span>
        </a>
        <form id="student-sidebar-logout" action="{{ route('student.logout') }}" method="POST" hidden>@csrf</form>
    </nav>

    <div class="student-sidebar-footer">
        <span class="material-symbols-outlined">verified_user</span>
        <span>Keep your account details safe.</span>
    </div>
</aside>
