@extends('layout',['title'=>'Profil Pentadbir'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Akaun Kakitangan</p><h2>Profil Saya</h2><p>Kemaskini identiti log masuk dan keselamatan akaun anda.</p></div></div>
<div class="admin-account-grid">
<aside class="panel admin-account-summary">
    <span class="admin-profile-avatar">{{ collect(explode(' ',$user->name))->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->implode('') }}</span>
    <h3>{{ $user->name }}</h3><p>{{ $user->email }}</p><span class="admin-role-badge">{{ $user->role_label }}</span>
    <dl><div><dt>Status akaun</dt><dd><span class="account-active-dot"></span>Aktif</dd></div><div><dt>Log masuk terakhir</dt><dd>{{ $user->last_login_at?->format('d/m/Y, H:i')??'Belum direkodkan' }}</dd></div><div><dt>Ahli sejak</dt><dd>{{ $user->created_at?->format('d/m/Y') }}</dd></div></dl>
</aside>
<form class="panel admin-profile-form" method="post" action="{{ route('admin.profile.update') }}">@csrf @method('PUT')
    <div class="panel-head"><div><h3>Maklumat Akaun</h3><p>Alamat e-mel digunakan untuk log masuk ke sistem.</p></div></div>
    @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="form-grid">
        <div class="form-group full"><label for="admin-name">Nama penuh <span class="required-mark">*</span></label><input class="field" id="admin-name" name="name" value="{{ old('name',$user->name) }}" maxlength="255" autocomplete="name" required></div>
        <div class="form-group full"><label for="admin-email">Alamat e-mel <span class="required-mark">*</span></label><input class="field" id="admin-email" name="email" type="email" value="{{ old('email',$user->email) }}" maxlength="255" autocomplete="email" required></div>
    </div>
    <div class="account-security-heading"><h3>Tukar Kata Laluan</h3><p>Biarkan kosong jika anda tidak mahu menukar kata laluan.</p></div>
    <div class="form-grid">
        <div class="form-group full"><label for="current-password">Kata laluan semasa</label><input class="field" id="current-password" name="current_password" type="password" autocomplete="current-password"></div>
        <div class="form-group"><label for="new-password">Kata laluan baharu</label><input class="field" id="new-password" name="password" type="password" autocomplete="new-password" aria-describedby="password-help"><small class="field-help" id="password-help">Minimum 8 aksara, termasuk huruf dan nombor.</small></div>
        <div class="form-group"><label for="password-confirmation">Sahkan kata laluan baharu</label><input class="field" id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan Profil</button></div>
</form>
</div>
@endsection
