@extends('layout', ['title' => 'Kawalan Ciri'])

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">{{ __('ui.pentadbiran_sistem.f78f3faf') }}</p>
        <h2>{{ __('ui.kawalan_ciri.4798a2e4') }}</h2>
        <p>{{ __('ui.aktifkan_atau_matikan_ciri_yang_masih_dalam.4160c4b9') }}</p>
    </div>
</div>

<section class="panel settings-section">
    <div class="settings-section-icon notification" aria-hidden="true">ID</div>
    <div class="settings-section-copy">
        <h3>{{ __('ui.pengesahan_kad_oku_mykad.53c6f66b') }}</h3>
        <p>{{ __('ui.kawal_keperluan_pengesahan_identiti_untuk_semua_pengguna.d71ad700') }}</p>
    </div>
    <form class="settings-fields" method="post" action="{{ route('admin.feature-controls.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="identity_verification_enabled" value="0">
        <label class="setting-toggle">
            <input type="checkbox" name="identity_verification_enabled" value="1" @checked($identityVerificationEnabled)>
            <span>
                <strong>{{ __('ui.aktifkan_pengesahan_identiti.febd63fc') }}</strong>
                <small>{{ __('ui.apabila_aktif_pengguna_oku_yang_belum_disahkan.e4217b8a') }}</small>
            </span>
        </label>

        <div class="{{ $identityVerificationEnabled ? 'notice' : 'error' }}" role="status">
            <strong>Status semasa: {{ $identityVerificationEnabled ? 'AKTIF' : 'TIDAK AKTIF' }}</strong><br>
            @if($identityVerificationEnabled)
                Sekatan MyKad sedang digunakan untuk pengguna OKU.
            @else
                Pengguna OKU boleh menggunakan sistem tanpa melengkapkan pengesahan MyKad. Rekod sedia ada tidak dipadam.
            @endif
        </div>

        <button class="btn btn-primary" type="submit">{{ __('ui.simpan_kawalan_ciri.dd3f282a') }}</button>
    </form>
</section>

<div class="error" role="note">
    <strong>{{ __('ui.perhatian.b4690881') }}</strong> {{ __('ui.perubahan_ini_berkuat_kuasa_serta_merta_untuk.ce6141df') }}
</div>
@endsection
