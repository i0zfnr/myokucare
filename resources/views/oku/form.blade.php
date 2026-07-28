@extends('layout',['title'=>$oku->exists?'Kemaskini OKU':'Daftar OKU'])
@section('content')
@php $value=fn($field,$default='')=>old($field,$oku->exists?$oku->{$field}:$default); @endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.pengurusan_oku.da88493c') }}</p><h2>{{ $oku->exists?'Kemaskini Rekod OKU':'Daftar Rekod Baharu' }}</h2><p>{{ __('ui.medan_bertanda.9cb5e501') }} <span class="required-mark">*</span> {{ __('ui.wajib_dilengkapkan.8a733d97') }}</p></div>
    <a class="btn" href="{{ $oku->exists?route('oku.show',$oku):route('oku.index') }}">← Kembali</a>
</div>

@if(!$oku->exists)
<section class="panel import-panel" aria-labelledby="import-title">
    <div class="import-copy"><span class="import-icon" aria-hidden="true">⇧</span><div><h3 id="import-title">{{ __('ui.import_senarai_oku.8df48cd2') }}</h3><p>{{ __('ui.muat_naik_csv_atau_xlsx_sehingga_10.2aaf7125') }}</p><a href="{{ route('oku.import-template') }}">{{ __('ui.muat_turun_templat_csv.fdcec864') }}</a></div></div>
    <form class="import-form" method="post" action="{{ route('oku.import') }}" enctype="multipart/form-data">@csrf
        <label for="import_file">{{ __('ui.pilih_fail_csv_atau_xlsx.1490e5b7') }}</label>
        <div><input class="field @error('import_file') is-invalid @enderror" id="import_file" name="import_file" type="file" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required aria-describedby="import-help import_file-error"><button class="btn btn-primary" type="submit">{{ __('ui.import_data.0021d0fb') }}</button></div>
        <small class="field-help" id="import-help">{{ __('ui.format_csv_atau_xlsx_maksimum_10_mb.a8bdc71d') }}</small>
        @error('import_file')<span class="field-error" id="import_file-error" role="alert">{{ $message }}</span>@enderror
    </form>
</section>
@if(session('import_result')) @php $result=session('import_result'); @endphp
<section class="import-result" role="status" aria-live="polite"><strong>{{ __('ui.import_selesai.d0c9cadc') }}</strong><span>{{ $result['imported'] }} berjaya</span><span>{{ $result['duplicates'] }} pendua dilangkau</span><span>{{ $result['failed'] }} gagal</span>@if($result['errors'])<details><summary>{{ __('ui.lihat_ralat_baris.3323ca10') }}</summary><ul>@foreach($result['errors'] as $error)<li>{{ $error }}</li>@endforeach</ul></details>@endif</section>
@endif
<div class="form-divider"><span>{{ __('ui.atau_daftar_satu_rekod_secara_manual.9fc249c5') }}</span></div>
@endif

@if($errors->any()&&!$errors->has('import_file'))
<div class="error form-error-summary" role="alert"><strong>{{ __('ui.sila_semak_semula_maklumat_berikut.c61e80ed') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<form class="oku-record-form" method="post" action="{{ $oku->exists?route('oku.update',$oku):route('oku.store') }}" enctype="multipart/form-data" novalidate>
@csrf @if($oku->exists) @method('PUT') @endif

<section class="panel form-section" aria-labelledby="personal-title">
<div class="form-section-head"><span aria-hidden="true">01</span><div><h3 id="personal-title">{{ __('ui.maklumat_peribadi.d33af1a7') }}</h3><p>{{ __('ui.maklumat_pengenalan_asas_pemegang_kad_oku.5d34f013') }}</p></div></div>
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
<div class="form-group full"><label for="address">{{ __('ui.alamat.85b6ed5c') }} <span class="required-mark">*</span></label><textarea class="textarea @error('address') is-invalid @enderror" id="address" name="address" rows="3" maxlength="1000" autocomplete="street-address" required aria-required="true" @error('address') aria-invalid="true" aria-describedby="address-error" @enderror>{{ $value('address') }}</textarea>@error('address')<span class="field-error" id="address-error">{{ $message }}</span>@enderror</div>
</div></section>

<section class="panel form-section" aria-labelledby="oku-title">
<div class="form-section-head"><span aria-hidden="true">02</span><div><h3 id="oku-title">{{ __('ui.pendaftaran_oku.7135182a') }}</h3><p>{{ __('ui.maklumat_kad_kategori_pendidikan_dan_bantuan.f67b5d8a') }}</p></div></div>
<div class="form-grid">
@foreach(['oku_card_number'=>['Nombor kad OKU',50]] as $field=>[$label,$max])
<div class="form-group"><label for="{{ $field }}">{{ $label }} <span class="required-mark">*</span></label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" value="{{ $value($field) }}" maxlength="{{ $max }}" required aria-required="true" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>@error($field)<span class="field-error" id="{{ $field }}-error">{{ $message }}</span>@enderror</div>
@endforeach
<x-education-level-field
    label="Tahap pendidikan"
    :value="$value('education_level')"
    required-label='<span class="required-mark">*</span>'
    :error="$errors->first('education_level')"
/>
<div class="form-group"><label for="oku_category">{{ __('ui.kategori_oku.5a4ba70d') }} <span class="required-mark">*</span></label><select class="select @error('oku_category') is-invalid @enderror" id="oku_category" name="oku_category" required aria-required="true"><option value="">{{ __('ui.pilih_kategori.5322c62f') }}</option>@foreach(['Fizikal','Penglihatan','Pendengaran','Pertuturan','Pembelajaran','Mental','Pelbagai'] as $option)<option value="{{ $option }}" @selected($value('oku_category')===$option)>{{ $option }}</option>@endforeach</select>@error('oku_category')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="assistance_type">{{ __('ui.jenis_bantuan_teks.c870fb89') }}</label><input class="field @error('assistance_type') is-invalid @enderror" id="assistance_type" name="assistance_type" value="{{ $value('assistance_type') }}" maxlength="255" placeholder="{{ __('ui.jika_berkenaan.8ae1b525') }}">@error('assistance_type')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group full"><label>{{ __('ui.jenis_bantuan_pilihan.888631e1') }}</label><div style="display:flex;flex-wrap:wrap;gap:8px">@foreach(['EPOKU'=>'Elaun Pekerja OKU','BTB'=>'Bantuan OKU Tidak Berupaya Bekerja','BPT'=>'Bantuan Penjagaan OKU Terlantar','BAT'=>'Bantuan Alat Sokongan/Tiruan','Lain-lain'=>'Lain-lain','Tiada'=>'Tiada'] as $val=>$label)<label class="check-option" style="margin:0!important"><input name="jenis_bantuan[]" type="checkbox" value="{{ $val }}" @if(is_array($value('jenis_bantuan',[]))&&in_array($val,$value('jenis_bantuan',[]),true)) checked @endif><span>{{ $label }}</span></label>@endforeach</div></div>
</div></section>

<section class="panel form-section" aria-labelledby="work-title">
<div class="form-section-head"><span aria-hidden="true">03</span><div><h3 id="work-title">{{ __('ui.pekerjaan.7b75a808') }}</h3><p>{{ __('ui.status_dan_pekerjaan_semasa_individu.fff97977') }}</p></div></div>
<div class="form-grid">
<div class="form-group"><label for="employment_status">{{ __('ui.status_pekerjaan.7cb12093') }} <span class="required-mark">*</span></label><select class="select @error('employment_status') is-invalid @enderror" id="employment_status" name="employment_status" required>@foreach(['Bekerja','Tidak Bekerja','Sendiri'] as $option)<option value="{{ $option }}" @selected($value('employment_status','Tidak Bekerja')===$option)>{{ $option }}</option>@endforeach</select>@error('employment_status')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="sektor_pekerjaan">{{ __('ui.sektor_pekerjaan.0e1545e5') }}</label><select class="select @error('sektor_pekerjaan') is-invalid @enderror" id="sektor_pekerjaan" name="sektor_pekerjaan"><option value="">{{ __('ui.pilih_sektor.3c0e74ef') }}</option>@foreach(['Sektor Awam','Sektor Swasta','Bekerja Sendiri','Tidak Bekerja'] as $option)<option value="{{ $option }}" @selected($value('sektor_pekerjaan')===$option)>{{ $option }}</option>@endforeach</select>@error('sektor_pekerjaan')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="job_name">{{ __('ui.nama_pekerjaan.2748b6f5') }} <small class="conditional-required">{{ __('ui.wajib_jika_bekerja.ae9c5fe8') }}</small></label><input class="field @error('job_name') is-invalid @enderror" id="job_name" name="job_name" value="{{ $value('job_name') }}" maxlength="255" autocomplete="organization-title">@error('job_name')<span class="field-error">{{ $message }}</span>@enderror</div>
</div></section>

<section class="panel form-section" aria-labelledby="contact-title">
<div class="form-section-head"><span aria-hidden="true">04</span><div><h3 id="contact-title">{{ __('ui.hubungan_akses.3c89d190') }}</h3><p>{{ __('ui.maklumat_untuk_dihubungi_dan_capaian_digital.45d7f365') }}</p></div></div>
<div class="form-grid">
@foreach(['phone_number'=>['Nombor telefon','tel',20],'email'=>['Alamat e-mel','email',255],'emergency_contact_name'=>['Nama hubungan kecemasan','text',255],'emergency_contact_phone'=>['Telefon hubungan kecemasan','tel',20]] as $field=>[$label,$type,$max])
<div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input class="field @error($field) is-invalid @enderror" id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value($field) }}" maxlength="{{ $max }}" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>@error($field)<span class="field-error" id="{{ $field }}-error">{{ $message }}</span>@enderror</div>
@endforeach
<fieldset class="form-group full choice-group"><legend>{{ __('ui.akses_dan_status_rekod.69a024ff') }}</legend>
@foreach(['has_smartphone'=>['Mempunyai telefon pintar',true],'has_internet'=>['Mempunyai akses internet',false],'is_active'=>['Rekod aktif',true]] as $field=>[$label,$default])
<label class="check-option" for="{{ $field }}"><input type="hidden" name="{{ $field }}" value="0"><input id="{{ $field }}" name="{{ $field }}" type="checkbox" value="1" @checked((bool)$value($field,$default))><span>{{ $label }}</span></label>
@endforeach
</fieldset></div></section>

<section class="panel form-section" aria-labelledby="document-title">
<div class="form-section-head"><span aria-hidden="true">05</span><div><h3 id="document-title">{{ __('ui.dokumen_gambar.c817437b') }}</h3><p>{{ __('ui.muat_naik_imej_yang_jelas_untuk_semakan.e00af69f') }}</p></div></div>
<div class="form-grid document-upload-grid">
    <div class="form-group upload-field">
        <label for="oku_card_image">{{ __('ui.imej_kad_oku.ec8d2f69') }}</label>
        <input class="field @error('oku_card_image') is-invalid @enderror" id="oku_card_image" name="oku_card_image" type="file" accept="image/jpeg,image/png,image/webp" data-file-input data-file-status="oku-card-status" @error('oku_card_image') aria-invalid="true" aria-describedby="oku_card_image-error" @enderror>
        <small class="field-help" id="oku-card-status">{{ $oku->oku_card_image_path ? 'Kad OKU telah dimuat naik. Pilih fail baharu hanya untuk menggantikannya.' : 'JPG, PNG atau WebP. Maksimum 5 MB. Pastikan semua maklumat boleh dibaca.' }}</small>
        @if($oku->exists && $oku->oku_card_image_path)<a class="document-link" href="{{ route('oku.document',[$oku,'card']) }}">{{ __('ui.lihat_kad_oku_semasa.43d77f61') }}</a>@endif
        @error('oku_card_image')<span class="field-error" id="oku_card_image-error">{{ $message }}</span>@enderror
    </div>
    <div class="form-group upload-field">
        <label for="profile_photo">{{ __('ui.gambar_profil.3b8d5cad') }}</label>
        <input class="field @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" data-file-input data-file-status="profile-photo-status" @error('profile_photo') aria-invalid="true" aria-describedby="profile_photo-error" @enderror>
        <small class="field-help" id="profile-photo-status">{{ $oku->profile_photo_path ? 'Gambar profil telah dimuat naik. Pilih fail baharu untuk menggantikannya.' : 'Pilihan. JPG, PNG atau WebP, maksimum 3 MB.' }}</small>
        @error('profile_photo')<span class="field-error" id="profile_photo-error">{{ $message }}</span>@enderror
    </div>
</div></section>

<div class="form-actions"><a class="btn" href="{{ $oku->exists?route('oku.show',$oku):route('oku.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $oku->exists?'Simpan Perubahan':'Daftar Rekod OKU' }}</button></div>
</form>
@endsection
