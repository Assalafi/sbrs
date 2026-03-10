<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Applicant Portal') - {{ setting('site_name', 'SBRS Portal') }}</title>
    @php
        $faviconPath = setting('favicon');
        $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) . '?v=' . time() : null;
    @endphp
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    @include('partials.meta')
    @include('partials.styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .main-content { padding-left: 0 !important; margin-left: 0 !important; }
        .status-badge { padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.875rem; font-weight: 500; }
        .status-registered { background: #6c757d; color: white; }
        .status-payment_pending { background: #ffc107; color: #212529; }
        .status-form_filling { background: #17a2b8; color: white; }
        .status-submitted { background: #0d6efd; color: white; }
        .status-under_review { background: #fd7e14; color: white; }
        .status-approved { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }
        .status-admitted { background: #006633; color: white; }
    </style>
    @stack('styles')
</head>

<body class="boxed-size">
    @include('partials.preloader')

    <div class="container-fluid">
        <div class="main-content d-flex flex-column">
            @include('partials.header-applicant')

            <div class="main-content-container overflow-hidden">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            <div class="flex-grow-1"></div>
            @include('partials.footer')
        </div>
    </div>

    @include('partials.theme_settings')
    @include('partials.scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @stack('scripts')
</body>

</html>
