@extends('layout',['title'=>$managedUser->exists?'Kemaskini Pengguna':'Daftar Pengguna'])
@section('content')
@php
$value=fn($field,$default='')=>old($field,$managedUser->exists?$managedUser->{$field}:$default);
$roleLabels=['super_admin'=>'Admin System','jkm_officer'=>'Pegawai JKM','employer'=>'Majikan','oku_user'=>'Pengguna OKU'];
$returnRole=$managedUser->exists?$managedUser->role:request('role');
$returnUrl=$returnRole?route('admin.users.role',$returnRole):route('admin.users.index');
@endphp
<div class="page-head"><div><p class="eyebrow">{{ __('ui.pentadbiran_sistem.f78f3faf') }}</p><h2>{{ $managedUser->exists?'Kemaskini Akaun':'Daftar Pengguna Baharu' }}</h2><p>{{ __('ui.tetapkan_identiti_peranan_dan_akses_pengguna_dengan.b3440cf6') }}</p></div><a class="btn" href="{{ $returnUrl }}">{{ __('ui.kembali.0b8ff91a') }}</a></div>
@if($errors->any())<div class="error form-error-summary" role="alert"><strong>{{ __('ui.sila_semak_maklumat_berikut.f29d1fe9') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="oku-record-form admin-user-form" method="post" action="{{ $managedUser->exists?route('admin.users.update',$managedUser):route('admin.users.store') }}">@csrf @if($managedUser->exists)@method('PUT')@endif
<section class="panel form-section"><div class="form-section-head"><span>01</span><div><h3>{{ __('ui.identiti_akaun.06f277a0') }}</h3><p>{{ __('ui.maklumat_yang_digunakan_untuk_log_masuk.222cd735') }}</p></div></div><div class="form-grid">
<div class="form-group"><label for="name">{{ __('ui.nama_penuh.46f89b95') }} <span class="required-mark">*</span></label><input class="field" id="name" name="name" value="{{ $value('name') }}" required>@error('name')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="email">{{ __('ui.alamat_e_mel.8e5b16c4') }} <span class="required-mark">*</span></label><input class="field" id="email" name="email" type="email" value="{{ $value('email') }}" required>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
</div></section>
<section class="panel form-section"><div class="form-section-head"><span>02</span><div><h3>{{ __('ui.peranan_pautan_profil.13e37934') }}</h3><p>{{ __('ui.akses_sistem_berubah_mengikut_peranan_yang_dipilih.d0d5d3bc') }}</p></div></div><div class="form-grid">
<div class="form-group"><label for="managed-role">{{ __('ui.peranan.0ef21dad') }} <span class="required-mark">*</span></label><select class="select" id="managed-role" name="role" data-role-select required>@foreach($roleLabels as $role=>$label)<option value="{{ $role }}" @selected($value('role',request('role','oku_user'))===$role)>{{ $label }}</option>@endforeach</select>@error('role')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group role-link-field" data-role-link="employer"><label for="employer_id">{{ __('ui.profil_majikan.ed137c8e') }} <span class="required-mark">*</span></label><select class="select" id="employer_id" name="employer_id"><option value="">{{ __('ui.pilih_majikan.b0d41b1f') }}</option>@foreach($employers as $employer)<option value="{{ $employer->id }}" @selected((string)$value('employer_id')===(string)$employer->id)>{{ $employer->company_name }}</option>@endforeach</select>@error('employer_id')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group role-link-field" data-role-link="oku_user"><label for="oku_id">{{ __('ui.profil_oku_dipautkan.ea35a83d') }} <span class="required-mark">*</span></label><select class="select" id="oku_id" name="oku_id"><option value="">{{ __('ui.pilih_profil_oku.87d0b52e') }}</option>@foreach($okus as $oku)<option value="{{ $oku->id }}" @selected((string)$value('oku_id')===(string)$oku->id)>{{ $oku->name }} — {{ $oku->oku_card_number }}</option>@endforeach</select>@error('oku_id')<span class="field-error">{{ $message }}</span>@enderror</div>
<fieldset class="form-group choice-group"><legend>{{ __('ui.status_akaun.ef78db19') }}</legend><label class="check-option"><input type="hidden" name="is_active" value="0"><input name="is_active" type="checkbox" value="1" @checked((bool)$value('is_active',true))><span>{{ __('ui.akaun_aktif_dan_boleh_log_masuk.05727098') }}</span></label></fieldset>
</div></section>
<section class="panel form-section"><div class="form-section-head"><span>03</span><div><h3>{{ __('ui.kebenaran_terperinci.1fcf1ce3') }}</h3><p>{{ __('ui.pilih_hanya_tindakan_yang_diperlukan_kebenaran_data.27f3d7f6') }}</p></div></div>
<div class="form-grid">@foreach($permissions as $permission)<label class="check-option"><input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', $managedUser->permissions ?? []), true))><span>{{ $permission }}</span></label>@endforeach</div>
</section>
<section class="panel form-section"><div class="form-section-head"><span>04</span><div><h3>{{ $managedUser->exists?'Tetapkan Semula Kata Laluan':'Kata Laluan Sementara' }}</h3><p>{{ $managedUser->exists?'Biarkan kosong jika tidak mahu menukar kata laluan.':'Pengguna boleh menukarnya kemudian melalui profil sendiri.' }}</p></div></div><div class="form-grid">
<div class="form-group"><label for="password">Kata laluan @unless($managedUser->exists)<span class="required-mark">*</span>@endunless</label><input class="field" id="password" name="password" type="password" autocomplete="new-password" @required(!$managedUser->exists)>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="password_confirmation">{{ __('ui.sahkan_kata_laluan.5243c157') }}</label><input class="field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
</div></section>
<div class="form-actions"><a class="btn" href="{{ $returnUrl }}">{{ __('ui.batal.1433539c') }}</a><button class="btn btn-primary" type="submit">{{ $managedUser->exists?'Simpan Perubahan':'Cipta Akaun' }}</button></div>
</form>
@endsection
