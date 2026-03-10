<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - {{ setting('site_name', 'SBRS Portal') }}</title>
    @php
        $faviconPath = setting('favicon');
        $faviconUrl = $faviconPath ? asset('storage/' . $faviconPath) : url('/assets/images/favicon.png');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}">
    @include('partials.meta')
    @include('partials.styles')
    @stack('styles')
</head>
<body class="boxed-size bg-white">
    @include('partials.preloader')

    <div class="container">
        <div class="main-content d-flex flex-column p-0">
            <div class="m-auto m-1230">
                <div class="row align-items-center">
                    <div class="col-lg-6 d-none d-lg-block">
                        @php
                            $authSettingKey = View::yieldContent('auth-image-key', '');
                            $authFallback = View::yieldContent('auth-image', 'login.jpg');
                            $authSettingImage = $authSettingKey ? setting_image($authSettingKey) : null;
                            $authImgSrc = $authSettingImage ?: asset('assets/images/' . $authFallback);
                        @endphp
                        <img src="{{ $authImgSrc }}" class="rounded-3 w-100" alt="auth">
                    </div>
                    <div class="col-lg-6">
                        <div class="mw-480 ms-lg-auto">
                            <div class="text-center mb-4">
                                <a href="{{ url('/') }}" class="d-inline-block">
                                    <img src="{{ setting_image('main_logo') ?? url('/assets/images/favicon.png') }}" alt="Logo" class="rounded-3" style="height:80px;">
                                </a>
                            </div>
                            <h3 class="fs-28 mb-2">@yield('auth-title', 'Welcome to SBRS Portal')</h3>
                            <p class="fw-medium fs-16 mb-4 text-muted">@yield('auth-subtitle', 'University of Maiduguri')</p>

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
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0 ps-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.theme_settings')
    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
