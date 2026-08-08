<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,nofollow">
    @include('partials.pwa-head')
    <title>{{ __('auth_recovery.forgot_title') }} — MyOKUcare</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#recovery-form">{{ __('auth_recovery.skip_to_form') }}</a>
<main class="login-page">
    <section class="login-brand" aria-labelledby="brand-heading">
        <div class="login-copy login-brand-content">
            <span class="login-logo"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <h1 id="brand-heading">{{ __('auth_recovery.recovery_heading') }}</h1>
            <p>{{ __('auth_recovery.recovery_intro') }}</p>
        </div>
    </section>
    <section class="login-form-wrap" aria-labelledby="form-heading">
        <a class="btn-ghost welcome-button" href="{{ route('login') }}">{{ __('auth_recovery.back_to_login') }}</a>
        <form id="recovery-form" class="login-form login-form-box" method="post" action="{{ route('password.email') }}">
            @csrf
            <p class="eyebrow">MyOKUcare</p>
            <h2 id="form-heading">{{ __('auth_recovery.forgot_title') }}</h2>
            <p class="login-intro">{{ __('auth_recovery.forgot_instructions') }}</p>
            @if(session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
            <div class="form-group">
                <label for="email">{{ __('auth_recovery.email') }}</label>
                <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<p class="field-error" id="email-error">{{ $message }}</p>@enderror
            </div>
            <button class="btn btn-primary login-submit" type="submit">{{ __('auth_recovery.send_reset_link') }}</button>
            <div class="login-note" role="note">{{ __('auth_recovery.contact_jkm') }}</div>
        </form>
    </section>
</main>
</body>
</html>
