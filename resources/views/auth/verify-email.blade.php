<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    @include('partials.pwa-head')
    <title>{{ __('auth_recovery.verify_title') }} — MyOKUcare</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<main class="login-page">
    <section class="login-brand" aria-labelledby="brand-heading">
        <div class="login-copy login-brand-content">
            <span class="login-logo"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <h1 id="brand-heading">{{ __('auth_recovery.verify_heading') }}</h1>
            <p>{{ __('auth_recovery.verify_intro', ['email' => auth()->user()->email]) }}</p>
        </div>
    </section>
    <section class="login-form-wrap" aria-labelledby="form-heading">
        <div class="login-form login-form-box">
            <h2 id="form-heading">{{ __('auth_recovery.verify_title') }}</h2>
            @if(session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
            @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
            <p class="login-intro">{{ __('auth_recovery.verify_instructions') }}</p>
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button class="btn btn-primary login-submit" type="submit">{{ __('auth_recovery.resend_verification') }}</button>
            </form>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="btn-ghost" type="submit">{{ __('auth_recovery.logout') }}</button>
            </form>
        </div>
    </section>
</main>
</body>
</html>
