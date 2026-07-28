<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Log masuk ke MyOKUcare menggunakan e-mel dan kata laluan akaun anda.">
    <meta property="og:title" content="Log Masuk — MyOKUcare">
    <meta property="og:description" content="Log masuk ke MyOKUcare — platform digital JKM untuk pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan kebajikan.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/myokucare-logo.png') }}">
    @include('partials.pwa-head')
    <title>{{ __('ui.log_masuk_myokucare.9ebbc976') }}</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#login-form">{{ __('ui.langkau_ke_borang_log_masuk.a07d02a0') }}</a>
<main class="login-page">
    <section class="login-brand" aria-labelledby="brand-heading">
        <div class="login-blob login-blob-1"></div>
        <div class="login-blob login-blob-2"></div>
        <div class="login-blob login-blob-3"></div>
        <div class="login-copy login-brand-content">
            <span class="login-logo"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <h1 id="brand-heading">{{ __('ui.sokongan_inklusif_dalam_satu_sistem.e6d237d7') }}</h1>
            <p>{{ __('ui.myokucare_menghubungkan_pengurusan_oku_peluang_pekerjaan_dan.6ddd6f92') }}</p>
            <div class="login-benefits" aria-label="{{ __('ui.kelebihan_sistem.c8311f90') }}">
                <span>{{ __('ui.satu_akaun_mengikut_peranan.d8088328') }}</span>
                <span>{{ __('ui.akses_mesra_peranti_mudah_alih.6a6d298f') }}</span>
            </div>
        </div>
    </section>

    <section class="login-form-wrap" aria-labelledby="login-heading">
        <a class="btn-ghost welcome-button" href="{{ route('welcome') }}" aria-label="{{ __('ui.kembali_ke_laman_utama.8cf65bd1') }}">{{ __('ui.kembali_ke_laman_utama.1a885223') }}</a>

        <form id="login-form" class="login-form login-form-box" method="post" action="{{ route('login.store') }}" data-login-form aria-label="{{ __('ui.borang_log_masuk.76cb95bb') }}">
            @csrf
            <p class="eyebrow">{{ __('ui.selamat_kembali.9380dd79') }}</p>
            <h2 id="login-heading">{{ __('ui.log_masuk_myokucare.9b368e79') }}</h2>
            <p class="login-intro">{{ __('ui.gunakan_e_mel_dan_kata_laluan_yang.8f8c52f0') }}</p>

            @if(session('success'))
                <div class="notice" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="error" role="alert" aria-live="assertive">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label for="email">{{ __('ui.alamat_e_mel.8e5b16c4') }} <span class="required-note">{{ __('ui.wajib.e6bc023e') }}</span></label>
                <input
                    class="field"
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                    placeholder="nama@contoh.my"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @else aria-invalid="false" @enderror
                >
                @error('email')<p class="field-error" id="email-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="password">{{ __('ui.kata_laluan.8c4f8a5c') }} <span class="required-note">{{ __('ui.wajib.e6bc023e') }}</span></label>
                <div class="password-field">
                    <input
                        class="field"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="{{ __('ui.masukkan_kata_laluan.a45a8b18') }}"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @else aria-invalid="false" @enderror
                    >
                    <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="{{ __('ui.tunjukkan_kata_laluan.9423350c') }}" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/>
                        </svg>
                    </button>
                </div>
                @error('password')<p class="field-error" id="password-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <label class="remember" for="remember">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                <span>{{ __('ui.ingat_saya_pada_peranti_ini.5d81162d') }}</span>
            </label>

            <button class="btn btn-primary login-submit btn-ripple" type="submit" data-login-submit>
                <span data-login-submit-label>{{ __('ui.log_masuk.65586411') }}</span>
            </button>

            <div class="login-note" role="note">{{ __('ui.semua_peranan_menggunakan_halaman_log_masuk_yang.235e3fa3') }}</div>
            <p class="login-footer">{{ __('ui.belum_mempunyai_akaun.03e573d5') }} <a href="{{ route('register') }}">{{ __('ui.daftar_sekarang.18b852d9') }}</a> · <a href="{{ route('welcome') }}">{{ __('ui.laman_utama.c456d24d') }}</a></p>
        </form>
    </section>
</main>
</body>
</html>
