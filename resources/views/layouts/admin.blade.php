<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ setting('site_name', 'SBRS Portal') }}</title>
    @php
        $faviconPath = setting('favicon');
        $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) . '?v=' . time() : null;
    @endphp
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" href="{{ $faviconUrl }}">
    @endif
    @include('partials.meta')
    @include('partials.styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-portal.css') }}?v=20261012">
    @stack('styles')
</head>

<body class="boxed-size admin-portal">
    @include('partials.preloader')
    @include('partials.sidebar')

    <div class="container-fluid">
        <div class="main-content d-flex flex-column">
            @include('partials.header')

            <main class="main-content-container overflow-hidden" id="admin-main-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show admin-flash" role="alert">
                        <span class="material-symbols-outlined align-middle me-1">check_circle</span>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show admin-flash" role="alert">
                        <span class="material-symbols-outlined align-middle me-1">error</span>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger alert-dismissible fade show admin-flash" role="alert">
                        <span class="material-symbols-outlined align-middle me-1">error</span>
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
    @stack('scripts')
</body>

</html>
