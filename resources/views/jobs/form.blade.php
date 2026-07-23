@extends('layout',['title'=>$job->exists?'Kemaskini Jawatan':'Tambah Jawatan'])
@section('content')
@php $value=fn($field,$default='')=>old($field,$job->exists?$job->{$field}:$default); @endphp
<div class="page-head"><div><p class="eyebrow">Pekerjaan Inklusif</p><h2>{{ $job->exists?'Kemaskini Peluang Kerja':'Tambah Peluang Kerja' }}</h2><p>Terbitkan jawatan dengan keperluan dan kesesuaian OKU yang jelas.</p></div><a class="btn" href="{{ route('jobs.index') }}">← Kembali</a></div>
@if($errors->any())<div class="error form-error-summary" role="alert"><strong>Sila semak maklumat berikut:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="oku-record-form" method="post" action="{{ $job->exists?route('jobs.update',$job):route('jobs.store') }}">@csrf @if($job->exists)@method('PUT')@endif
<section class="panel form-section"><div class="form-section-head"><span>01</span><div><h3>Maklumat Jawatan</h3><p>Maklumat asas iklan dan organisasi.</p></div></div><div class="form-grid">
<div class="form-group"><label for="employer_id">Majikan <span class="required-mark">*</span></label><select class="select" id="employer_id" name="employer_id" required><option value="">Pilih majikan</option>@foreach($employers as $employer)<option value="{{ $employer->id }}" @selected((string)$value('employer_id')===(string)$employer->id)>{{ $employer->company_name }}</option>@endforeach</select>@error('employer_id')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="title">Nama jawatan <span class="required-mark">*</span></label><input class="field" id="title" name="title" value="{{ $value('title') }}" required>@error('title')<span class="field-error">{{ $message }}</span>@enderror</div>
<div class="form-group"><label for="location">Lokasi <span class="required-mark">*</span></label><input class="field" id="location" name="location" value="{{ $value('location') }}" required></div>
<div class="form-group"><label for="employment_type">Jenis pekerjaan <span class="required-mark">*</span></label><select class="select" id="employment_type" name="employment_type" required>@foreach(['Sepenuh Masa','Separuh Masa','Kontrak','Sementara'] as $option)<option @selected($value('employment_type','Sepenuh Masa')===$option)>{{ $option }}</option>@endforeach</select></div>
<div class="form-group full"><label for="description">Penerangan jawatan <span class="required-mark">*</span></label><textarea class="textarea" id="description" name="description" rows="4" required>{{ $value('description') }}</textarea></div>
</div></section>
<section class="panel form-section"><div class="form-section-head"><span>02</span><div><h3>Kesesuaian & Keperluan</h3><p>Nyatakan kategori OKU dan skop kerja dengan terang.</p></div></div><div class="form-grid">
<div class="form-group"><label for="oku_category_suitable">Kategori OKU sesuai <span class="required-mark">*</span></label><select class="select" id="oku_category_suitable" name="oku_category_suitable" required>@foreach(['Semua','Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $option)<option @selected($value('oku_category_suitable','Semua')===$option)>{{ $option }}</option>@endforeach</select></div>
<div class="form-group"><label for="working_hours">Waktu bekerja</label><input class="field" id="working_hours" name="working_hours" value="{{ $value('working_hours') }}"></div>
<div class="form-group full"><label for="requirements">Keperluan jawatan <span class="required-mark">*</span></label><textarea class="textarea" id="requirements" name="requirements" rows="4" required>{{ $value('requirements') }}</textarea></div>
<div class="form-group full"><label for="responsibilities">Tanggungjawab</label><textarea class="textarea" id="responsibilities" name="responsibilities" rows="4">{{ $value('responsibilities') }}</textarea></div>
</div></section>
<section class="panel form-section"><div class="form-section-head"><span>03</span><div><h3>Gaji & Penerbitan</h3><p>Tetapkan julat gaji dan tempoh permohonan.</p></div></div><div class="form-grid">
<div class="form-group"><label for="salary_min">Gaji minimum (RM) <span class="required-mark">*</span></label><input class="field" id="salary_min" name="salary_min" type="number" min="0" step="50" value="{{ $value('salary_min') }}" required></div>
<div class="form-group"><label for="salary_max">Gaji maksimum (RM)</label><input class="field" id="salary_max" name="salary_max" type="number" min="0" step="50" value="{{ $value('salary_max') }}"></div>
<div class="form-group"><label for="application_deadline">Tarikh tutup</label><input class="field" id="application_deadline" name="application_deadline" type="date" value="{{ old('application_deadline',$job->application_deadline?->format('Y-m-d')) }}"></div>
<fieldset class="form-group choice-group"><legend>Status iklan</legend><label class="check-option"><input type="hidden" name="is_active" value="0"><input name="is_active" type="checkbox" value="1" @checked((bool)$value('is_active',true))><span>Iklan aktif</span></label></fieldset>
</div></section>
<div class="form-actions"><a class="btn" href="{{ route('jobs.index') }}">Batal</a><button class="btn btn-primary" type="submit">{{ $job->exists?'Simpan Perubahan':'Terbitkan Jawatan' }}</button></div>
</form>
@endsection
