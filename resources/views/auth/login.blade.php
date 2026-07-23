<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    @include('partials.pwa-head')
    <title>Log Masuk — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        .login-page{min-height:100vh;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(420px,.75fr);background:#fff}
        .login-brand{position:relative;overflow:hidden;display:flex;align-items:flex-end;padding:clamp(42px,7vw,96px);color:#fff;background:linear-gradient(145deg,#ff9064 0%,#ff6565 58%,#d9434c 100%)}
        .login-brand:before,.login-brand:after{content:"";position:absolute;border:1px solid rgba(255,255,255,.18);border-radius:50%}
        .login-brand:before{width:520px;height:520px;right:-190px;top:-160px}.login-brand:after{width:340px;height:340px;right:70px;top:40px}
        .login-copy{position:relative;z-index:1;max-width:590px}.login-logo{width:58px;height:58px;display:grid;place-items:center;border-radius:17px;background:#fff;color:var(--primary-dark);font-size:27px;font-weight:950;box-shadow:0 18px 40px rgba(112,31,38,.2)}
        .login-copy h1{margin:28px 0 18px;font-size:clamp(38px,5vw,66px);line-height:1.04;letter-spacing:-.045em}.login-copy p{max-width:520px;margin:0;color:rgba(255,255,255,.84);font-size:16px;line-height:1.7}
        .login-form-wrap{position:relative;display:grid;place-items:center;padding:42px}.login-form{width:min(420px,100%)}.login-form h2{margin:0;font-size:30px;letter-spacing:-.03em}.login-form>p{margin:9px 0 30px;color:var(--muted);font-size:13px;line-height:1.6}
        .welcome-button{position:absolute;top:28px;right:32px;min-height:42px}
        .login-form .form-group{margin-bottom:17px}.login-form .field{min-height:50px}.remember{display:flex;align-items:center;gap:8px;margin:3px 0 22px;color:var(--muted);font-size:12px}.login-submit{width:100%;min-height:50px}
        .login-note{margin-top:24px;padding:14px;border:1px solid var(--line);border-radius:11px;color:var(--muted);background:var(--canvas);font-size:11px;line-height:1.6}
        @media(max-width:850px){.login-page{grid-template-columns:1fr}.login-brand{min-height:290px;align-items:center;padding:38px 24px}.login-copy h1{font-size:38px}.login-copy p{font-size:14px}.login-form-wrap{padding:92px 22px 42px}.welcome-button{top:24px;right:22px;left:22px}}
    </style>
</head>
<body>
<main class="login-page">
    <section class="login-brand"><div class="login-copy"><span class="login-logo">M</span><h1>Sokongan inklusif, dalam satu sistem.</h1><p>MyOKUcare menghubungkan pengurusan OKU, peluang pekerjaan dan bantuan kebajikan untuk setiap peranan yang terlibat.</p></div></section>
    <section class="login-form-wrap">
        <a class="btn welcome-button" href="{{ route('welcome') }}">← Kembali ke Laman Utama</a>
        <form class="login-form" method="post" action="{{ route('login.store') }}">@csrf
            <p class="eyebrow">Selamat Kembali</p><h2>Log masuk MyOKUcare</h2><p>Gunakan e-mel dan kata laluan yang didaftarkan untuk akaun anda.</p>
            @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
            <div class="form-group"><label for="email">Alamat e-mel</label><input class="field" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="nama@contoh.my"></div>
            <div class="form-group"><label for="password">Kata laluan</label><input class="field" id="password" name="password" type="password" autocomplete="current-password" required placeholder="Masukkan kata laluan"></div>
            <label class="remember"><input name="remember" type="checkbox" value="1"> Ingat saya pada peranti ini</label>
            <button class="btn btn-primary login-submit" type="submit">Log Masuk</button>
            <div class="login-note">Semua peranan menggunakan halaman log masuk yang sama. Paparan dan akses sistem akan disesuaikan secara automatik mengikut peranan akaun.</div>
            <p style="margin-top:20px;text-align:center;color:var(--muted);font-size:12px">Belum mempunyai akaun? <a style="color:var(--primary-dark);font-weight:850" href="{{ route('register') }}">Daftar sekarang</a> · <a href="{{ route('welcome') }}">Laman utama</a></p>
        </form>
    </section>
</main>
</body></html>
