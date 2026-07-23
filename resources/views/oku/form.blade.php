@extends('layout',['title'=>$oku->exists?'Kemaskini OKU':'Daftar OKU'])
@section('content')
@php $value=fn($field,$default='')=>old($field,$oku->exists?$oku->{$field}:$default); @endphp
<div class="page-head">
    <div><p class="eyebrow">Pengurusan OKU</p><h2>{{ $oku->exists?'Kemaskini Rekod OKU':'Daftar Rekod Baharu' }}</h2><p>Medan bertanda <span class="required-mark">*</span> wajib dilengkapkan.</p></div>
    <a class="btn" href="{{ $oku->exists?route('oku.show',$oku):route('oku.index') }}">← Kembali</a>
</div>

@if(!$oku->exists)
<section class="panel import-panel" aria-labelledby="import-title">
    <div class="import-copy"><span class="import-icon" aria-hidden="true">⇧</span><div><h3 id="import-title">Import Senarai OKU</h3><p>Muat naik CSV atau XLSX sehingga 10 MB. Rekod pendua dilangkau dan baris bermasalah dilaporkan.</p><a href="{{ route('oku.import-template') }}">Muat turun templat CSV</a></div></div>
    <form class="import-form" method="post" action="{{ route('oku.import') }}" enctype="multipart/form-data">@csrf
        <label for="import_file">Pilih fail CSV atau XLSX</label>
        <div><input class="field @error('import_file') is-invalid @enderror" id="import_file" name="import_file" type="file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required aria-describedby="import-help import_file-error"><button class="btn btn-primary" type="submit">Import Data</button></div>
        <small class="field-help" id="import-help">Format .csv atau .xlsx, maksimum 10 MB.</small>
        @error('import_file')<span class="field-error" id="import_file-error" role="alert">{{ $message }}</span>@enderror
    </form>
</section>
@if(session('import_result')) @php $result=session('import_result'); @endphp
<section class="import-result" role="status" aria-live="polite"><strong>Import selesai</strong><span>{{ $result['imported'] }} berjaya</span><span>{{ $result['duplicates'] }} pendua dilangkau</span><span>{{ $result['failed'] }} gagal</span>@if($result['errors'])<details><summary>Lihat ralat baris</summary><ul>@foreach($result['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></details>@endif</section>
@endif
<div class="form-divider"><span>atau daftar satu rekod secara manual</span></div>
@endif

@if($errors->any()&&!$errors->has('import_file'))
<div class="error form-error-summary" role="alert"><strong>Sila semak semula maklumat berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form class="oku-record-form" method="post" action="{{ $oku->exists?route('oku.update',$oku):route('oku.store') }}" enctype="multipart/form-data" novalidate>
@csrf @if($oku->exists) @method('PUT') @endif

<section class="panel form-section" aria-labelledby="personal-title">
<div class="form-section-head"><span aria-hidden="true">01</span><div><h3 id="personal-title">Maklumat Peribadi</h3><p>Maklumat pengenalan asas pemegang kad OKU.</p></div></div>
<div class="form-grid">
@foreach([
 ['name','Nama penuh','text','255','name','full'],
 ['ic_number','Nombor kad pengenalan','text','20','off',''],
 ['age','Umur','number','3','off',''],
] as [$field,$label,$type,$max,$autocomplete,$class])
<div class="form-group {{ $class }}"><label for="{{ $field }}">{{ $label }} <span class="required-mark">*</span></label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value($field) }}" @if($field==='age') min="1" max="120" @else maxlength="{{ $max }}" @endif autocomplete="{{ $autocomplete }}" @if(in_array($field,['ic_number','age'])) inputmode="numeric" @endif required aria-required="true" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>@error($field)<span class="field-error" id="{{ $field }}-error">{{ $message }}</span>@enderror</div>
@endforeach
@foreach(['gender'=>['Jantina',['Lelaki','Perempuan']],'marital_status'=>['Status perkahwinan',['Berkahwin','Bujang','Duda','Janda']]] as $field=>[$label,$options])
<div class="form-group"><label for="{{ $field }}">{{ $label }} <span class="required-mark">*</span></label><select class="select @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" required aria-required="true" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror><option value="">Pilih {{ strtolower($label) }}</option>@foreach($options as $option)<option value="{{ $option }}" @selected($value($field)===$option)>{{ $option }}</option>@endforeach</select>@error($field)<span class="field-error" id="{{ $field }}-error">{{ $message }}</span>@enderror</div>
@endforeach
<div class="form-group full"><label for="address">Alamat <span class="required-mark">*</span></label><textarea class="textarea @error('address') is-invalid @enderror" id="address" name="address" rows="3" maxlength="1000" autocomplete="street-address" required aria-required="true" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ $value('address') }}</textarea>@error('address')<span class="field-error" id="address-error">{{ $message }}</span>@enderror</div>
</div></section>

<section class="panel form-section" aria-labelledby="oku-title">
<div class="form-section-head"><span aria-hidden="true">02</span><div><h3 id="oku-title">Pendaftaran OKU</h3><p>Maklumat kad, kategori, pendidikan dan bantuan.</p></div></div>
<div class="form-grid">
@foreach(['oku_card_number'=>['Nombor kad OKU',50],'education_level'=>['Tahap pendidikan',100]] as $field=>[$label,$max])
<div class="form-group"><label for="{{ $field }}">{{ $label }} <span class="required-mark">*</span></label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" value="{{ $value($field) }}" maxlength="{{ $max }}" required aria-required="true" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>@error($field)<span class="field-error" id="{{ $field }}-error">{{ $message }}</span>@enderror</div>
@endforeach
<div class="form-group"><label for="oku_category">Kategori OKU <span class="required-mark">*</span></label><select class="select @error('oku_category') is-invalid @enderror" id="oku_category" name="oku_category" required aria-required="true"><option value="">Pilih kategori</option>@foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $option)<option value="{{ $option }}" @selected($value('oku_category')===$option)>{{ $option }}</option>@endforeach</select>@error('oku_category')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="assistance_type">Jenis bantuan</label><input class="field @error('assistance_type') is-invalid @enderror" id="assistance_type" name="assistance_type" value="{{ $value('assistance_type') }}" maxlength="255" placeholder="Jika berkenaan">@error('assistance_type')<span class="field-error">{{ $message }}</span>@enderror</div>
</div></section>

<section class="panel form-section" aria-labelledby="work-title">
<div class="form-section-head"><span aria-hidden="true">03</span><div><h3 id="work-title">Pekerjaan</h3><p>Status dan pekerjaan semasa individu.</p></div></div>
<div class="form-grid">
<div class="form-group"><label for="employment_status">Status pekerjaan <span class="required-mark">*</span></label><select class="select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status" required>@foreach(['Bekerja','Tidak Bekerja','Sendiri'] as $option)<option value="{{ $option }}" @selected($value('employment_status','Tidak Bekerja')===$option)>{{ $option }}</option>@endforeach</select>@error('employment_status')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="job_name">Nama pekerjaan <small class="conditional-required">Wajib jika bekerja</small></label><input class="field @error('job_name') is-invalid @enderror" id="job_name" name="job_name" value="{{ $value('job_name') }}" maxlength="255" autocomplete="organization-title">@error('job_name')<span class="field-error">{{ $message }}</span>@enderror</div>
</div></section>

<section class="panel form-section" aria-labelledby="contact-title">
<div class="form-section-head"><span aria-hidden="true">04</span><div><h3 id="contact-title">Hubungan & Akses</h3><p>Maklumat untuk dihubungi dan capaian digital.</p></div></div>
<div class="form-grid">
@foreach(['phone_number'=>['Nombor telefon','tel',20],'email'=>['Alamat e-mel','email',255],'emergency_contact_name'=>['Nama hubungan kecemasan','text',255],'emergency_contact_phone'=>['Telefon hubungan kecemasan','tel',20]] as $field=>[$label,$type,$max])
<div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value($field) }}" maxlength="{{ $max }}" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>@error($field)<span class="field-error" id="{{ $field }}-error">{{ $message }}</span>@enderror</div>
@endforeach
<fieldset class="form-group full choice-group"><legend>Akses dan status rekod</legend>
@foreach(['has_smartphone'=>['Mempunyai telefon pintar',true],'has_internet'=>['Mempunyai akses internet',false],'is_active'=>['Rekod aktif',true]] as $field=>[$label,$default])
<label class="check-option" for="{{ $field }}"><input type="hidden" name="{{ $field }}" value="0"><input id="{{ $field }}" name="{{ $field }}" type="checkbox" value="1" @checked((bool)$value($field,$default))><span>{{ $label }}</span></label>
@endforeach
</fieldset></div></section>

<section class="panel form-section" aria-labelledby="document-title">
<div class="form-section-head"><span aria-hidden="true">05</span><div><h3 id="document-title">Dokumen & Gambar</h3><p>Muat naik imej yang jelas untuk semakan identiti dan paparan profil.</p></div></div>
<div class="form-grid document-upload-grid">
    <div class="form-group upload-field">
        <label for="oku_card_image">Imej Kad OKU</label>
        <input class="field @error('oku_card_image') is-invalid @enderror" id="oku_card_image" name="oku_card_image" type="file" accept="image/jpeg,image/png,image/webp" data-file-input data-file-status="oku-card-status" @error('oku_card_image') aria-invalid="true" aria-describedby="oku_card_image-error" @enderror>
        <small class="field-help" id="oku-card-status">{{ $oku->oku_card_image_path ? 'Kad OKU telah dimuat naik. Pilih fail baharu hanya untuk menggantikannya.' : 'JPG, PNG atau WebP. Maksimum 5 MB. Pastikan semua maklumat boleh dibaca.' }}</small>
        @if($oku->exists && $oku->oku_card_image_path)<a class="document-link" href="{{ route('oku.document',[$oku,'card']) }}">Lihat Kad OKU semasa</a>@endif
        @error('oku_card_image')<span class="field-error" id="oku_card_image-error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group upload-field">
        <label for="profile_photo">Gambar Profil</label>
        <input class="field @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" data-file-input data-file-status="profile-photo-status" @error('profile_photo') aria-invalid="true" aria-describedby="profile_photo-error" @enderror>
        <small class="field-help" id="profile-photo-status">{{ $oku->profile_photo_path ? 'Gambar profil telah dimuat naik. Pilih fail baharu untuk menggantikannya.' : 'Pilihan. JPG, PNG atau WebP, maksimum 3 MB.' }}</small>
        @error('profile_photo')<span class="field-error" id="profile_photo-error">{{ $message }}</span>@enderror
    </div>
</div></section>

<div class="form-actions"><a class="btn" href="{{ $oku->exists?route('oku.show',$oku):route('oku.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $oku->exists?'Simpan Perubahan':'Daftar Rekod OKU' }}</button></div>
</form>
@endsection
