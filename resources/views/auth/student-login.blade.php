<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Login - {{ setting('site_name', 'SBRS Portal') }}</title>
    @include('partials.meta')
    @include('partials.styles')
    <link rel="stylesheet" href="{{ asset('assets/css/student-portal.css') }}?v=20261012">
</head>
<body class="student-login-page student-portal">
    @include('partials.preloader')
    <div class="student-login-shell">
        <section class="student-login-visual" aria-label="Student portal introduction">
            <img class="student-login-logo" src="{{ setting_image('main_logo') ?? asset('assets/images/favicon.png') }}" alt="{{ setting('site_name', 'SBRS Portal') }}">
            <h1>Everything you need for your student journey.</h1>
            <p>Access registration, payments, hostel services and academic records from one secure portal.</p>
            <div class="student-login-feature"><span class="material-symbols-outlined">verified</span><span>One account for all student services</span></div>
            <div class="student-login-feature"><span class="material-symbols-outlined">phone_iphone</span><span>Designed for phone, tablet and desktop</span></div>
            <div class="student-login-feature"><span class="material-symbols-outlined">shield_lock</span><span>Your personal information stays protected</span></div>
        </section>
        <section class="student-login-form-side">
            <div class="student-login-form-wrap">
                <a class="student-login-brand" href="{{ url('/') }}">
                    <img src="{{ setting_image('main_logo') ?? asset('assets/images/favicon.png') }}" alt="">
                    <strong>{{ setting('site_name', 'SBRS Portal') }}</strong>
                </a>
                <div class="student-login-heading">
                    <span class="eyebrow">Student access</span>
                    <h2>Welcome back</h2>
                    <p>Sign in with your registration number or email address.</p>
                </div>
                @if($errors->any())
                    <div class="student-login-alert mb-3" role="alert">
                        <span class="material-symbols-outlined align-middle me-1" style="font-size:18px">error</span>
                        {{ $errors->first() }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="student-login-alert mb-3" role="alert">{{ session('error') }}</div>
                @endif
                <form method="POST" action="{{ route('student.login') }}" id="student-login-form">
                    @csrf
                    <div class="mb-3">
                        <label class="student-login-label" for="login">Registration number or email</label>
                        <div class="student-login-input-wrap">
                            <span class="material-symbols-outlined">badge</span>
                            <input id="login" type="text" class="form-control" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="e.g. SBRS/IJMB/2026/001">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="student-login-label" for="password">Password</label>
                        <div class="student-login-input-wrap">
                            <span class="material-symbols-outlined">lock</span>
                            <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="Enter your password" style="padding-right:2.8rem">
                            <button class="login-password-toggle" type="button" id="toggle-password" aria-label="Show password"><span class="material-symbols-outlined">visibility</span></button>
                        </div>
                    </div>
                    <div class="student-login-options">
                        <label class="form-check mb-0"><input class="form-check-input" type="checkbox" name="remember" id="remember"><span class="ms-1">Remember me</span></label>
                        <a href="{{ route('student.forgot') }}">Forgot password?</a>
                    </div>
                    <button type="submit" class="student-login-button"><span class="material-symbols-outlined">login</span>Sign in</button>
                </form>
                <p class="student-login-footer">Default password is the password used during your application.<br><a href="{{ url('/') }}">Return to homepage</a></p>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('password');
            const button = document.getElementById('toggle-password');
            button?.addEventListener('click', function () {
                const visible = input.type === 'text';
                input.type = visible ? 'password' : 'text';
                button.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
                button.innerHTML = '<span class="material-symbols-outlined">' + (visible ? 'visibility' : 'visibility_off') + '</span>';
            });
            document.getElementById('student-login-form')?.addEventListener('submit', function () {
                const button = this.querySelector('button[type="submit"]');
                if (button) { button.disabled = true; button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Signing in…'; }
            });
        });
    </script>
</body>
</html>
