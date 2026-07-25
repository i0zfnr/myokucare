<!doctype html>
<html lang="ms">
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
    <title>Log Masuk — MyOKUcare</title>
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#login-form">Langkau ke borang log masuk</a>
<main class="login-page">
    <section class="login-brand" aria-labelledby="brand-heading">
        <div class="login-blob login-blob-1"></div>
        <div class="login-blob login-blob-2"></div>
        <div class="login-blob login-blob-3"></div>
        <div class="login-copy login-brand-content">
            <span class="login-logo"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <h1 id="brand-heading">Sokongan inklusif, dalam satu sistem.</h1>
            <p>MyOKUcare menghubungkan pengurusan OKU, peluang pekerjaan dan bantuan kebajikan untuk setiap peranan yang terlibat.</p>
            <div class="login-benefits" aria-label="Kelebihan sistem">
                <span>Satu akaun mengikut peranan</span>
                <span>Akses mesra peranti mudah alih</span>
            </div>
        </div>
    </section>

    <section class="login-form-wrap" aria-labelledby="login-heading">
        <a class="btn-ghost welcome-button" href="{{ route('welcome') }}" aria-label="Kembali ke laman utama">← Kembali ke Laman Utama</a>

        <form id="login-form" class="login-form login-form-box" method="post" action="{{ route('login.store') }}" data-login-form aria-label="Borang log masuk">
            @csrf
            <p class="eyebrow">Selamat Kembali</p>
            <h2 id="login-heading">Log masuk MyOKUcare</h2>
            <p class="login-intro">Gunakan e-mel dan kata laluan yang didaftarkan untuk akaun anda.</p>

            @if(session('success'))
                <div class="notice" role="status">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="error" role="alert" aria-live="assertive">{{ $errors->first() }}</div>
            @endif

            <div class="form-group">
                <label for="email">Alamat e-mel <span class="required-note">Wajib</span></label>
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
                <label for="password">Kata laluan <span class="required-note">Wajib</span></label>
                <div class="password-field">
                    <input
                        class="field"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        placeholder="Masukkan kata laluan"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @else aria-invalid="false" @enderror
                    >
                    <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Tunjukkan kata laluan" aria-pressed="false">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/>
                        </svg>
                    </button>
                </div>
                @error('password')<p class="field-error" id="password-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <label class="remember" for="remember">
                <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                <span>Ingat saya pada peranti ini</span>
            </label>

            <button class="btn btn-primary login-submit btn-ripple" type="submit" data-login-submit>
                <span data-login-submit-label>Log Masuk</span>
            </button>

            <div class="login-note" role="note">Semua peranan menggunakan halaman log masuk yang sama. Paparan dan akses sistem akan disesuaikan secara automatik mengikut peranan akaun.</div>
            <p class="login-footer">Belum mempunyai akaun? <a href="{{ route('register') }}">Daftar sekarang</a> · <a href="{{ route('welcome') }}">Laman utama</a></p>
        </form>
    </section>
</main>
</body>
</html>
