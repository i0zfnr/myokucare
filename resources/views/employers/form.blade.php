@extends('layout',['title'=>$employer->exists?'Kemaskini Majikan':'Daftar Majikan'])
@section('content')
@php $value=fn($field,$default='')=>old($field,$employer->exists?$employer->{$field}:$default); @endphp
<div class="page-head">
    <div><p class="eyebrow">Pengurusan Majikan</p><h2>{{ $employer->exists?'Kemaskini Profil Majikan':'Daftar Majikan Baharu' }}</h2><p>Lengkapkan profil organisasi untuk pengurusan peluang pekerjaan inklusif.</p></div>
    <a class="btn" href="{{ route('employers.index') }}">← Kembali</a>
</div>

@if($errors->any())<div class="error form-error-summary" role="alert"><strong>Sila semak maklumat berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<form class="oku-record-form" method="post" action="{{ $employer->exists?route('employers.update',$employer):route('employers.store') }}" novalidate>
@csrf @if($employer->exists) @method('PUT') @endif
<section class="panel form-section">
    <div class="form-section-head"><span aria-hidden="true">01</span><div><h3>Maklumat Syarikat</h3><p>Identiti rasmi dan profil asas organisasi.</p></div></div>
    <div class="form-grid">
        <div class="form-group"><label for="company_name">Nama syarikat <span class="required-mark">*</span></label><input class="field @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ $value('company_name') }}" maxlength="255" required>@error('company_name')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label for="registration_number">Nombor pendaftaran SSM <span class="required-mark">*</span></label><input class="field @error('registration_number') is-invalid @enderror" id="registration_number" name="registration_number" value="{{ $value('registration_number') }}" maxlength="50" required>@error('registration_number')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label for="industry_sector">Sektor industri <span class="required-mark">*</span></label><input class="field @error('industry_sector') is-invalid @enderror" id="industry_sector" name="industry_sector" value="{{ $value('industry_sector') }}" maxlength="100" required>@error('industry_sector')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group"><label for="number_of_employees">Jumlah pekerja</label><input class="field @error('number_of_employees') is-invalid @enderror" id="number_of_employees" name="number_of_employees" type="number" min="0" value="{{ $value('number_of_employees') }}">@error('number_of_employees')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group full"><label for="address">Alamat syarikat <span class="required-mark">*</span></label><textarea class="textarea @error('address') is-invalid @enderror" id="address" name="address" rows="3" required>{{ $value('address') }}</textarea>@error('address')<span class="field-error">{{ $message }}</span>@enderror</div>
        <div class="form-group full"><label for="company_description">Penerangan syarikat</label><textarea class="textarea @error('company_description') is-invalid @enderror" id="company_description" name="company_description" rows="4">{{ $value('company_description') }}</textarea>@error('company_description')<span class="field-error">{{ $message }}</span>@enderror</div>
    </div>
</section>
<section class="panel form-section">
    <div class="form-section-head"><span aria-hidden="true">02</span><div><h3>Maklumat Perhubungan</h3><p>Pegawai yang boleh dihubungi oleh JKM dan calon.</p></div></div>
    <div class="form-grid">
        @foreach([['contact_person','Nama pegawai','text'],['phone_number','Nombor telefon','tel'],['email','Alamat e-mel','email'],['website','Laman web','url']] as [$field,$label,$type])
        <div class="form-group"><label for="{{ $field }}">{{ $label }} @if($field!=='website')<span class="required-mark">*</span>@endif</label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value($field) }}" maxlength="255" @if($field!=='website') required @endif placeholder="{{ $field==='website'?'https://contoh.my':'' }}">@error($field)<span class="field-error">{{ $message }}</span>@enderror</div>
        @endforeach
    </div>
</section>
<section class="panel form-section">
    <div class="form-section-head"><span aria-hidden="true">03</span><div><h3>Status Organisasi</h3><p>Tetapkan keterlibatan majikan dalam program pekerjaan OKU.</p></div></div>
    <div class="form-grid"><fieldset class="form-group full choice-group"><legend>Status majikan</legend>
        <label class="check-option"><input type="hidden" name="has_oku_quota" value="0"><input name="has_oku_quota" type="checkbox" value="1" @checked((bool)$value('has_oku_quota',false))><span>Majikan mempunyai kuota atau peluang mesra OKU</span></label>
        <label class="check-option"><input type="hidden" name="is_active" value="0"><input name="is_active" type="checkbox" value="1" @checked((bool)$value('is_active',true))><span>Profil majikan aktif</span></label>
    </fieldset></div>
</section>
<div class="form-actions"><a class="btn" href="{{ route('employers.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $employer->exists?'Simpan Perubahan':'Daftar Majikan' }}</button></div>
</form>
@endsection
