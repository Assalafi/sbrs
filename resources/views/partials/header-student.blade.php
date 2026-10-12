@php $student = Auth::guard('student')->user(); @endphp
<header class="student-header" id="header-area">
    <div class="student-header-left">
        <button type="button" class="student-header-button" id="header-burger-menu" aria-label="Open navigation">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <div class="student-header-copy">
            <span class="student-header-eyebrow">{{ setting('site_name', 'SBRS Portal') }}</span>
            <span class="student-header-title">@yield('page-title', 'Student portal')</span>
        </div>
    </div>
    <div class="student-header-right">
        <button class="student-header-button switch-toggle settings-btn dark-btn" id="switch-toggle" type="button" aria-label="Toggle theme">
            <span class="dark"><span class="material-symbols-outlined">light_mode</span></span>
            <span class="light"><span class="material-symbols-outlined">dark_mode</span></span>
        </button>
        <div class="dropdown">
            <button class="student-profile-trigger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="student-header-avatar">
                    @if($student?->passport_photo)
                        <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="">
                    @else
                        {{ strtoupper(substr($student?->first_name ?? 'S', 0, 1) . substr($student?->surname ?? '', 0, 1)) }}
                    @endif
                </span>
                <span>{{ $student?->first_name ?? 'Student' }}</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end student-profile-dropdown">
                <div class="px-3 py-2 border-bottom mb-1">
                    <strong class="d-block small">{{ $student?->full_name ?? 'Student' }}</strong>
                    <small class="text-muted">{{ $student?->registration_number ?? '' }}</small>
                </div>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('student.dashboard') }}"><span class="material-symbols-outlined">space_dashboard</span>Dashboard</a>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('student.biodata.index') }}"><span class="material-symbols-outlined">badge</span>My bio-data</a>
                <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('student.password') }}"><span class="material-symbols-outlined">lock</span>Change password</a>
                <a class="dropdown-item d-flex align-items-center gap-2" href="#" onclick="event.preventDefault(); document.getElementById('student-header-logout').submit();"><span class="material-symbols-outlined">logout</span>Sign out</a>
                <form id="student-header-logout" action="{{ route('student.logout') }}" method="POST" hidden>@csrf</form>
            </div>
        </div>
    </div>
</header>
