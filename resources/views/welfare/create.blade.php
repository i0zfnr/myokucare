@extends('layout',['title'=>'Permohonan Kebajikan Baharu'])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.sokongan_kebajikan.db5367af') }}</p><h2>{{ __('ui.permohonan_baharu.b9deab2e') }}</h2><p>{{ __('ui.daftarkan_keperluan_bantuan_untuk_tindakan_dan_semakan.38ff40de') }}</p></div><a class="btn" href="{{ route('welfare.index') }}">{{ __('ui.kembali.0b8ff91a') }}</a></div>
@if(!$isStaff&&!$oku)
<section class="panel welfare-no-profile"><h3>{{ __('ui.profil_oku_belum_dipautkan.d94aba24') }}</h3><p>{{ __('ui.lengkapkan_profil_oku_terlebih_dahulu_sebelum_membuat.8e2a3a7c') }}</p>@if(auth()->user()->role==='oku_user')<a class="btn btn-primary" href="{{ route('career-profile.show') }}">{{ __('ui.lengkapkan_profil.3dac4b06') }}</a>@endif</section>
@else
@if($errors->any())<div class="error" role="alert"><strong>{{ __('ui.sila_semak_maklumat_permohonan.6873cc29') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="panel welfare-create-form" method="post" action="{{ route('welfare.store') }}">@csrf
    @if($isStaff)
    <div class="form-group full"><label for="oku_id">{{ __('ui.pemohon_oku.b88fe355') }} <span class="required-mark">*</span></label><select class="select" id="oku_id" name="oku_id" required><option value="">{{ __('ui.pilih_pemohon.39a86eff') }}</option>@foreach($okus as $person)<option value="{{ $person->id }}" @selected(old('oku_id')==$person->id)>{{ $person->name }} — {{ $person->oku_card_number }}</option>@endforeach</select></div>
    <div class="form-group"><label for="application_date">{{ __('ui.tarikh_permohonan.63c68240') }} <span class="required-mark">*</span></label><input class="field" id="application_date" name="application_date" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ old('application_date',today()->format('Y-m-d')) }}" required></div>
    @else
    <div class="welfare-applicant"><span>{{ __('ui.pemohon.115368c3') }}</span><strong>{{ $oku->name }}</strong><small>{{ $oku->oku_card_number }}</small></div>
    @endif
    <div class="form-group"><label for="application_type">{{ __('ui.jenis_permohonan.555e1b59') }} <span class="required-mark">*</span></label><input class="field" id="application_type" name="application_type" list="welfare-types" maxlength="100" value="{{ old('application_type') }}" placeholder="{{ __('ui.contoh_bantuan_alat_sokongan.fc7ddafd') }}" required><datalist id="welfare-types"><option value="Bantuan Alat Sokongan"><option value="Bantuan Kewangan"><option value="Bantuan Penjagaan"><option value="Bantuan Mobiliti"><option value="Bantuan Latihan"></datalist></div>
    <div class="form-group full"><label for="notes">{{ __('ui.penerangan_keperluan.4b54a6b5') }}</label><textarea class="textarea" id="notes" name="notes" rows="5" maxlength="2000" placeholder="{{ __('ui.terangkan_bantuan_yang_diperlukan_dan_maklumat_berkaitan.31c1a055') }}">{{ old('notes') }}</textarea><small class="field-help">{{ __('ui.maksimum_2_000_aksara_elakkan_memasukkan_maklumat.67091535') }}</small></div>
    <div class="form-actions full"><a class="btn" href="{{ route('welfare.index') }}">{{ __('ui.batal.1433539c') }}</a><button class="btn btn-primary" type="submit">{{ __('ui.hantar_permohonan.65bbe7a9') }}</button></div>
</form>
@endif
@endsection
