<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Portal') - {{ setting('site_name', 'SBRS Portal') }}</title>
    @php
        $faviconPath = setting('favicon');
        $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) . '?v=' . time() : null;
    @endphp
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    @include('partials.meta')
    @include('partials.styles')
    <link rel="stylesheet" href="{{ asset('assets/css/student-portal.css') }}?v=20261012-2">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    @stack('styles')
</head>

<body class="boxed-size student-portal">
    <a class="student-skip-link" href="#student-main-content">Skip to content</a>
    @include('partials.preloader')
    @include('partials.sidebar-student')

    <div class="container-fluid">
        <div class="main-content d-flex flex-column">
            @include('partials.header-student')

            <main class="main-content-container overflow-hidden" id="student-main-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show student-flash" role="alert">
                        <span class="material-symbols-outlined">check_circle</span>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show student-flash" role="alert">
                        <span class="material-symbols-outlined">error</span>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show student-flash" role="alert">
                        <span class="material-symbols-outlined">info</span>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show student-flash" role="alert">
                        <span class="material-symbols-outlined">error</span>
                        <span>
                            @foreach ($errors->all() as $error)
                                {{ $error }}@if(!$loop->last)<br>@endif
                            @endforeach
                        </span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <div class="flex-grow-1"></div>
            @include('partials.footer')
        </div>
    </div>

    @include('partials.theme_settings')
    @include('partials.scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('student-sidebar');
            const backdrop = document.getElementById('student-sidebar-backdrop');
            const openButton = document.getElementById('header-burger-menu');
            const closeButton = document.getElementById('sidebar-burger-menu');
            const closeSidebar = () => {
                sidebar?.classList.remove('is-open');
                backdrop?.classList.remove('is-visible');
                document.body.classList.remove('student-sidebar-open');
            };
            openButton?.addEventListener('click', () => {
                sidebar?.classList.add('is-open');
                backdrop?.classList.add('is-visible');
                document.body.classList.add('student-sidebar-open');
            });
            closeButton?.addEventListener('click', closeSidebar);
            backdrop?.addEventListener('click', closeSidebar);
            document.querySelectorAll('.student-nav-link').forEach(link => link.addEventListener('click', () => {
                if (window.innerWidth < 768) closeSidebar();
            }));
        });
    </script>
    @stack('scripts')
</body>

</html>
