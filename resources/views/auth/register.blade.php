<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#FF6565">
    <title>Daftar Akaun — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <style>
        .register-page{min-height:100vh;display:grid;grid-template-columns:minmax(360px,.7fr) minmax(0,1fr);background:#fff}.register-side{position:relative;overflow:hidden;padding:58px;display:flex;flex-direction:column;justify-content:space-between;color:#fff;background:linear-gradient(145deg,#ff9064,#ff6565 60%,#d9434c)}.register-side:after{content:"";position:absolute;width:440px;height:440px;left:-190px;bottom:-210px;border:70px solid rgba(255,255,255,.07);border-radius:50%}.register-brand{position:relative;z-index:1;display:flex;align-items:center;gap:12px;text-decoration:none}.register-brand .brand-mark{background:#fff;color:var(--primary-dark)}.register-message{position:relative;z-index:1}.register-message h1{margin:0 0 15px;font-size:43px;line-height:1.06;letter-spacing:-.045em}.register-message p{margin:0;color:rgba(255,255,255,.8);line-height:1.7}.register-wrap{display:grid;place-items:center;padding:46px 28px}.register-form{width:min(560px,100%)}.register-form h2{margin:0;font-size:30px}.register-form>p{margin:9px 0 28px;color:var(--muted);font-size:13px}.register-form .form-group{margin-bottom:15px}.register-form .field,.register-form .select{min-height:49px}.register-grid{display:grid;grid-template-columns:1fr 1fr;gap:15px}.register-submit{width:100%;min-height:50px;margin-top:5px}.signin-note{margin-top:20px;text-align:center;color:var(--muted);font-size:12px}.signin-note a{color:var(--primary-dark);font-weight:850}.back-home{position:absolute;top:22px;right:26px;color:var(--muted);font-size:12px;text-decoration:none}@media(max-width:820px){.register-page{grid-template-columns:1fr}.register-side{min-height:240px;padding:34px 24px}.register-message h1{font-size:32px}.register-wrap{padding:40px 20px}.back-home{display:none}}@media(max-width:540px){.register-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<main class="register-page">
    <section class="register-side"><a class="register-brand" href="{{ route('welcome') }}"><span class="brand-mark">M</span><strong>MyOKUcare</strong></a><div class="register-message"><h1>Sertai komuniti MyOKUcare.</h1><p>Cipta akaun menggunakan e-mel anda. Akses sistem akan disesuaikan berdasarkan peranan yang dipilih.</p></div></section>
    <section class="register-wrap"><a class="back-home" href="{{ route('welcome') }}">← Kembali ke laman utama</a><form class="register-form" method="post" action="{{ route('register.store') }}">@csrf
        <p class="eyebrow">Akaun Baharu</p><h2>Daftar MyOKUcare</h2><p>Lengkapkan maklumat berikut untuk mencipta akaun anda.</p>
        @if($errors->any())<div class="error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        <div class="form-group"><label for="name">Nama penuh</label><input class="field" id="name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
        <div class="register-grid"><div class="form-group"><label for="email">Alamat e-mel</label><input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"></div><div class="form-group"><label for="role">Daftar sebagai</label><select class="select" id="role" name="role" required><option value="">Pilih peranan</option><option value="oku_user" @selected(old('role')==='oku_user')>Pengguna OKU</option><option value="family_member" @selected(old('role')==='family_member')>Ahli Keluarga</option><option value="employer" @selected(old('role')==='employer')>Majikan</option></select></div></div>
        <div class="register-grid"><div class="form-group"><label for="password">Kata laluan</label><input class="field" id="password" name="password" type="password" required autocomplete="new-password"></div><div class="form-group"><label for="password_confirmation">Sahkan kata laluan</label><input class="field" id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"></div></div>
        <button class="btn btn-primary register-submit" type="submit">Cipta Akaun</button><p class="signin-note">Sudah mempunyai akaun? <a href="{{ route('login') }}">Log masuk di sini</a></p>
    </form></section>
</main>
</body></html>
