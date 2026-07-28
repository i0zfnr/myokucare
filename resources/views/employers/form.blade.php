@extends('layout',['title'=>$employer->exists?'Kemaskini Majikan':'Daftar Majikan'])
@section('content')
@php $value=fn($field,$default='')=>old($field,$employer->exists?$employer->{$field}:$default); @endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.pengurusan_majikan.76e02fcb') }}</p><h2>{{ $employer->exists?'Kemaskini Profil Majikan':'Daftar Majikan Baharu' }}</h2><p>{{ __('ui.lengkapkan_profil_organisasi_untuk_pengurusan_peluang_pekerjaan.9e464e9a') }}</p></div>
    <a class="btn" href="{{ route('employers.index') }}">{{ __('ui.kembali.0b8ff91a') }}</a>
</div>

@if($errors->any())<div class="error form-error-summary" role="alert"><strong>{{ __('ui.sila_semak_maklumat_berikut.f29d1fe9') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form class="oku-record-form" method="post" action="{{ $employer->exists?route('employers.update',$employer):route('employers.store') }}" novalidate>
@csrf @if($employer->exists) @method('PUT') @endif
<section class="panel form-section">
    <div class="form-section-head"><span aria-hidden="true">01</span><div><h3>{{ __('ui.maklumat_syarikat.2c92883f') }}</h3><p>{{ __('ui.identiti_rasmi_dan_profil_asas_organisasi.1de7d763') }}</p></div></div>
    <div class="form-grid">
        <div class="form-group"><label for="company_name">{{ __('ui.nama_syarikat.567263a3') }} <span class="required-mark">*</span></label><input class="field @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ $value('company_name') }}" maxlength="255" required>@error('company_name')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label for="registration_number">{{ __('ui.nombor_pendaftaran_ssm.c29ebca0') }} <span class="required-mark">*</span></label><input class="field @error('registration_number') is-invalid @enderror" id="registration_number" name="registration_number" value="{{ $value('registration_number') }}" maxlength="50" required>@error('registration_number')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label for="industry_sector">{{ __('ui.sektor_industri.1f53ae4e') }} <span class="required-mark">*</span></label><input class="field @error('industry_sector') is-invalid @enderror" id="industry_sector" name="industry_sector" value="{{ $value('industry_sector') }}" maxlength="100" required>@error('industry_sector')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label for="number_of_employees">{{ __('ui.jumlah_pekerja.820f5831') }}</label><input class="field @error('number_of_employees') is-invalid @enderror" id="number_of_employees" name="number_of_employees" type="number" min="0" value="{{ $value('number_of_employees') }}">@error('number_of_employees')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group full"><label for="address">{{ __('ui.alamat_syarikat.663fc2c3') }} <span class="required-mark">*</span></label><textarea class="textarea @error('address') is-invalid @enderror" id="address" name="address" rows="3" required>{{ $value('address') }}</textarea>@error('address')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group full"><label for="company_description">{{ __('ui.penerangan_syarikat.80cafda2') }}</label><textarea class="textarea @error('company_description') is-invalid @enderror" id="company_description" name="company_description" rows="4">{{ $value('company_description') }}</textarea>@error('company_description')<span class="field-error">{{ $message }}</span>@enderror</div>
    </div>
</section>
<section class="panel form-section">
    <div class="form-section-head"><span aria-hidden="true">02</span><div><h3>{{ __('ui.maklumat_perhubungan.7f8bcd14') }}</h3><p>{{ __('ui.pegawai_yang_boleh_dihubungi_oleh_jkm_dan.3dd2cfc0') }}</p></div></div>
    <div class="form-grid">
        @foreach([['contact_person','Nama pegawai','text'],['phone_number','Nombor telefon','tel'],['email','Alamat e-mel','email'],['website','Laman web','url']] as [$field,$label,$type])
        <div class="form-group"><label for="{{ $field }}">{{ $label }} @if($field!=='website')<span class="required-mark">*</span>@endif</label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value($field) }}" maxlength="255" @if($field!=='website') required @endif placeholder="{{ $field==='website'?'https://contoh.my':'' }}">@error($field)<span class="field-error">{{ $message }}</span>@enderror</div>
        @endforeach
    </div>
</section>
<section class="panel form-section">
    <div class="form-section-head"><span aria-hidden="true">03</span><div><h3>{{ __('ui.status_organisasi.775f0aa4') }}</h3><p>{{ __('ui.tetapkan_keterlibatan_majikan_dalam_program_pekerjaan_oku.ef2efa01') }}</p></div></div>
    <div class="form-grid"><fieldset class="form-group full choice-group"><legend>{{ __('ui.status_majikan.e7be7080') }}</legend>
        <label class="check-option"><input type="hidden" name="has_oku_quota" value="0"><input name="has_oku_quota" type="checkbox" value="1" @checked((bool)$value('has_oku_quota',false))><span>{{ __('ui.majikan_mempunyai_kuota_atau_peluang_mesra_oku.f99453bc') }}</span></label>
        <label class="check-option"><input type="hidden" name="is_active" value="0"><input name="is_active" type="checkbox" value="1" @checked((bool)$value('is_active',true))><span>{{ __('ui.profil_majikan_aktif.03f248a8') }}</span></label>
    </fieldset></div>
</section>
<div class="form-actions"><a class="btn" href="{{ route('employers.index') }}">{{ __('ui.batal.1433539c') }}</a><button class="btn btn-primary" type="submit">{{ $employer->exists?'Simpan Perubahan':'Daftar Majikan' }}</button></div>
</form>
@endsection
