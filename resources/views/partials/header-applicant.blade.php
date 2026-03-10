<header class="header-area bg-white mb-4 rounded-bottom-15" id="header-area">
    <div class="row align-items-center">
        <div class="col-lg-6 col-sm-6">
            <div class="left-header-content">
                <ul class="d-flex align-items-center ps-0 mb-0 list-unstyled justify-content-center justify-content-sm-start">
                    <li>
                        <a href="{{ route('applicant.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                            <img src="{{ setting_image('main_logo') ?? url('/assets/images/favicon.png') }}" alt="Logo" class="wh-40 rounded-circle me-2">
                            <span class="fw-semibold text-dark fs-16">{{ setting('site_name', 'SBRS Portal') }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-6 col-sm-6">
            <div class="right-header-content mt-2 mt-sm-0">
                <ul class="d-flex align-items-center justify-content-center justify-content-sm-end ps-0 mb-0 list-unstyled">
                    <li class="header-right-item">
                        <div class="light-dark">
                            <button class="switch-toggle settings-btn dark-btn p-0 bg-transparent" id="switch-toggle">
                                <span class="dark"><i class="material-symbols-outlined">light_mode</i></span>
                                <span class="light"><i class="material-symbols-outlined">dark_mode</i></span>
                            </button>
                        </div>
                    </li>
                    <li class="header-right-item">
                        <div class="dropdown admin-profile">
                            <div class="d-xxl-flex align-items-center bg-transparent border-0 text-start p-0 cursor dropdown-toggle" data-bs-toggle="dropdown">
                                <div class="flex-shrink-0">
                                    @php $applicant = Auth::guard('applicant')->user(); @endphp
                                    @if($applicant && $applicant->passport_photo)
                                        <img class="rounded-circle wh-40" src="{{ asset('storage/' . $applicant->passport_photo) }}" alt="photo" />
                                    @else
                                        <div class="rounded-circle wh-40 bg-primary d-flex align-items-center justify-content-center text-white fw-bold">
                                            {{ $applicant ? strtoupper(substr($applicant->surname, 0, 1)) : 'A' }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-2">
                                    <div class="d-none d-xxl-block">
                                        <h3>{{ $applicant->full_name ?? 'Applicant' }}</h3>
                                        <span class="fs-12 text-muted">{{ $applicant->application_number ?? '' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-menu border-0 bg-white dropdown-menu-end">
                                <div class="d-flex align-items-center info">
                                    <div class="flex-grow-1 ms-2">
                                        <h3 class="fw-medium">{{ $applicant->full_name ?? 'Applicant' }}</h3>
                                        <span class="fs-12">{{ $applicant->email ?? '' }}</span>
                                    </div>
                                </div>
                                <ul class="admin-link ps-0 mb-0 list-unstyled">
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-body" href="{{ route('applicant.dashboard') }}">
                                            <i class="material-symbols-outlined">dashboard</i>
                                            <span class="ms-2">Dashboard</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item d-flex align-items-center text-body" href="#" onclick="event.preventDefault(); document.getElementById('applicant-logout-form').submit();">
                                            <i class="material-symbols-outlined">logout</i>
                                            <span class="ms-2">Logout</span>
                                        </a>
                                        <form id="applicant-logout-form" action="{{ route('applicant.logout') }}" method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
