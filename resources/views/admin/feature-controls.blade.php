@extends('layout', ['title' => 'Kawalan Ciri'])

@section('content')
<div class="page-head">
    <div>
        <p class="eyebrow">Pentadbiran Sistem</p>
        <h2>Kawalan Ciri</h2>
        <p>Aktifkan atau matikan ciri yang masih dalam fasa ujian.</p>
    </div>
</div>

<section class="panel settings-section">
    <div class="settings-section-icon notification" aria-hidden="true">ID</div>
    <div class="settings-section-copy">
        <h3>Pengesahan Kad OKU & MyKad</h3>
        <p>Kawal keperluan pengesahan identiti untuk semua pengguna OKU.</p>
    </div>
    <form class="settings-fields" method="post" action="{{ route('admin.feature-controls.update') }}">
        @csrf
        @method('PUT')
        <input type="hidden" name="identity_verification_enabled" value="0">
        <label class="setting-toggle">
            <input type="checkbox" name="identity_verification_enabled" value="1" @checked($identityVerificationEnabled)>
            <span>
                <strong>Aktifkan pengesahan identiti</strong>
                <small>Apabila aktif, pengguna OKU yang belum disahkan akan diwajibkan menghantar MyKad sebelum menggunakan sistem.</small>
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

        <button class="btn btn-primary" type="submit">Simpan Kawalan Ciri</button>
    </form>
</section>

<div class="error" role="note">
    <strong>Perhatian:</strong> Perubahan ini berkuat kuasa serta-merta untuk semua pengguna OKU. Gunakan suis ini untuk ujian atau kecemasan sahaja.
</div>
@endsection
