@extends('layout',['title'=>'Profil Admin System'])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.akaun_kakitangan.350b60bf') }}</p><h2>{{ __('ui.profil_saya.95c1e30b') }}</h2><p>{{ __('ui.kemaskini_identiti_log_masuk_dan_keselamatan_akaun.3a51c952') }}</p></div></div>
<div class="admin-account-grid">
<aside class="panel admin-account-summary">
    <span class="admin-profile-avatar">{{ collect(explode(' ',$user->name))->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->implode('') }}</span>
    <h3>{{ $user->name }}</h3><p>{{ $user->email }}</p><span class="admin-role-badge">{{ $user->role_label }}</span>
    <dl><div><dt>{{ __('ui.status_akaun.ef78db19') }}</dt><dd><span class="account-active-dot"></span>{{ __('ui.aktif.89f29d42') }}</dd></div><div><dt>{{ __('ui.log_masuk_terakhir.09b0af44') }}</dt><dd>{{ $user->last_login_at?->format('d/m/Y, H:i')??'Belum direkodkan' }}</dd></div><div><dt>{{ __('ui.ahli_sejak.fa62c921') }}</dt><dd>{{ $user->created_at?->format('d/m/Y') }}</dd></div></dl>
</aside>
<form class="panel admin-profile-form" method="post" action="{{ route('admin.profile.update') }}">@csrf @method('PUT')
    <div class="panel-head"><div><h3>{{ __('ui.maklumat_akaun.ba1bd9de') }}</h3><p>{{ __('ui.alamat_e_mel_digunakan_untuk_log_masuk.d4e7edb3') }}</p></div></div>
    @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
    <div class="form-grid">
        <div class="form-group full"><label for="admin-name">{{ __('ui.nama_penuh.46f89b95') }} <span class="required-mark">*</span></label><input class="field" id="admin-name" name="name" value="{{ old('name',$user->name) }}" maxlength="255" autocomplete="name" required></div>
        <div class="form-group full"><label for="admin-email">{{ __('ui.alamat_e_mel.8e5b16c4') }} <span class="required-mark">*</span></label><input class="field" id="admin-email" name="email" type="email" value="{{ old('email',$user->email) }}" maxlength="255" autocomplete="email" required></div>
    </div>
    <div class="account-security-heading"><h3>{{ __('ui.tukar_kata_laluan.79921d52') }}</h3><p>{{ __('ui.biarkan_kosong_jika_anda_tidak_mahu_menukar.b4659db3') }}</p></div>
    <div class="form-grid">
        <div class="form-group full"><label for="current-password">{{ __('ui.kata_laluan_semasa.16f381e5') }}</label><input class="field" id="current-password" name="current_password" type="password" autocomplete="current-password"></div>
        <div class="form-group"><label for="new-password">{{ __('ui.kata_laluan_baharu.a791d3d8') }}</label><input class="field" id="new-password" name="password" type="password" autocomplete="new-password" aria-describedby="password-help"><small class="field-help" id="password-help">{{ __('ui.minimum_8_aksara_termasuk_huruf_dan_nombor.173de21f') }}</small></div>
        <div class="form-group"><label for="password-confirmation">{{ __('ui.sahkan_kata_laluan_baharu.44ecf587') }}</label><input class="field" id="password-confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
    </div>
    <div class="form-actions"><button class="btn btn-primary" type="submit">{{ __('ui.simpan_profil.f7952d13') }}</button></div>
</form>
</div>
@endsection
