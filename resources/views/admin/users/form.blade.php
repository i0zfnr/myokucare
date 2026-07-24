@extends('layout',['title'=>$managedUser->exists?'Kemaskini Pengguna':'Daftar Pengguna'])
@section('content')
@php
$value=fn($field,$default='')=>old($field,$managedUser->exists?$managedUser->{$field}:$default);
$roleLabels=['super_admin'=>'Pentadbir','jkm_officer'=>'Pegawai JKM','employer'=>'Majikan','oku_user'=>'Pengguna OKU','family_member'=>'Ahli Keluarga','viewer'=>'Viewer'];
$returnRole=$managedUser->exists?$managedUser->role:request('role');
$returnUrl=$returnRole?route('admin.users.role',$returnRole):route('admin.users.index');
@endphp
<div class="page-head"><div><p class="eyebrow">Pentadbiran Sistem</p><h2>{{ $managedUser->exists?'Kemaskini Akaun':'Daftar Pengguna Baharu' }}</h2><p>Tetapkan identiti, peranan dan akses pengguna dengan berhati-hati.</p></div><a class="btn" href="{{ $returnUrl }}">← Kembali</a></div>
@if($errors->any())<div class="error form-error-summary" role="alert"><strong>Sila semak maklumat berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="oku-record-form admin-user-form" method="post" action="{{ $managedUser->exists?route('admin.users.update',$managedUser):route('admin.users.store') }}">@csrf @if($managedUser->exists)@method('PUT')@endif
<section class="panel form-section"><div class="form-section-head"><span>01</span><div><h3>Identiti Akaun</h3><p>Maklumat yang digunakan untuk log masuk.</p></div></div><div class="form-grid">
<div class="form-group"><label for="name">Nama penuh <span class="required-mark">*</span></label><input class="field" id="name" name="name" value="{{ $value('name') }}" required>@error('name')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="email">Alamat e-mel <span class="required-mark">*</span></label><input class="field" id="email" name="email" type="email" value="{{ $value('email') }}" required>@error('email')<span class="field-error">{{ $message }}</span>@enderror</div>
</div></section>
<section class="panel form-section"><div class="form-section-head"><span>02</span><div><h3>Peranan & Pautan Profil</h3><p>Akses sistem berubah mengikut peranan yang dipilih.</p></div></div><div class="form-grid">
<div class="form-group"><label for="managed-role">Peranan <span class="required-mark">*</span></label><select class="select" id="managed-role" name="role" data-role-select required>@foreach($roleLabels as $role=>$label)<option value="{{ $role }}" @selected($value('role',request('role','viewer'))===$role)>{{ $label }}</option>@endforeach</select>@error('role')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group role-link-field" data-role-link="employer"><label for="employer_id">Profil majikan <span class="required-mark">*</span></label><select class="select" id="employer_id" name="employer_id"><option value="">Pilih majikan</option>@foreach($employers as $employer)<option value="{{ $employer->id }}" @selected((string)$value('employer_id')===(string)$employer->id)>{{ $employer->company_name }}</option>@endforeach</select>@error('employer_id')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group role-link-field" data-role-link="oku_user,family_member"><label for="oku_id">Profil OKU dipautkan <span class="required-mark">*</span></label><select class="select" id="oku_id" name="oku_id"><option value="">Pilih profil OKU</option>@foreach($okus as $oku)<option value="{{ $oku->id }}" @selected((string)$value('oku_id')===(string)$oku->id)>{{ $oku->name }} — {{ $oku->oku_card_number }}</option>@endforeach</select>@error('oku_id')<span class="field-error">{{ $message }}</span>@enderror</div>
<fieldset class="form-group choice-group"><legend>Status akaun</legend><label class="check-option"><input type="hidden" name="is_active" value="0"><input name="is_active" type="checkbox" value="1" @checked((bool)$value('is_active',true))><span>Akaun aktif dan boleh log masuk</span></label></fieldset>
</div></section>
<section class="panel form-section"><div class="form-section-head"><span>03</span><div><h3>{{ $managedUser->exists?'Tetapkan Semula Kata Laluan':'Kata Laluan Sementara' }}</h3><p>{{ $managedUser->exists?'Biarkan kosong jika tidak mahu menukar kata laluan.':'Pengguna boleh menukarnya kemudian melalui profil sendiri.' }}</p></div></div><div class="form-grid">
<div class="form-group"><label for="password">Kata laluan @unless($managedUser->exists)<span class="required-mark">*</span>@endunless</label><input class="field" id="password" name="password" type="password" autocomplete="new-password" @required(!$managedUser->exists)>@error('password')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="password_confirmation">Sahkan kata laluan</label><input class="field" id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"></div>
</div></section>
<div class="form-actions"><a class="btn" href="{{ $returnUrl }}">Batal</a><button class="btn btn-primary" type="submit">{{ $managedUser->exists?'Simpan Perubahan':'Cipta Akaun' }}</button></div>
</form>
@endsection
