<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    @include('partials.pwa-head')
    <title>{{ __('auth_recovery.reset_title') }} — MyOKUcare</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#reset-form">{{ __('auth_recovery.skip_to_form') }}</a>
<main class="login-page">
    <section class="login-brand" aria-labelledby="brand-heading">
        <div class="login-copy login-brand-content">
            <span class="login-logo"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <h1 id="brand-heading">{{ __('auth_recovery.reset_heading') }}</h1>
            <p>{{ __('auth_recovery.reset_intro') }}</p>
        </div>
    </section>
    <section class="login-form-wrap" aria-labelledby="form-heading">
        <form id="reset-form" class="login-form login-form-box" method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <h2 id="form-heading">{{ __('auth_recovery.reset_title') }}</h2>
            @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
            <div class="form-group">
                <label for="email">{{ __('auth_recovery.email') }}</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="password">{{ __('auth_recovery.new_password') }}</label>
                <input class="field" id="password" name="password" type="password" autocomplete="new-password" required aria-describedby="password-help">
                <p class="login-note" id="password-help">{{ __('auth_recovery.password_requirements') }}</p>
            </div>
            <div class="form-group">
                <label for="password_confirmation">{{ __('auth_recovery.confirm_password') }}</label>
                <input class="field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary login-submit" type="submit">{{ __('auth_recovery.reset_password') }}</button>
        </form>
    </section>
</main>
</body>
</html>
