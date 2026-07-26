@extends('layout', ['title' => $employment->exists ? 'Kemaskini Pekerjaan' : 'Tambah Pekerjaan'])
@section('content')
@php $value=fn($field,$default='')=>old($field,$employment->exists?$employment->{$field}:$default); @endphp
<div class="page-head"><div><p class="eyebrow">Hubungan Pekerjaan</p><h2>{{ $employment->exists?'Kemaskini Rekod':'Tambah Rekod Pekerjaan' }}</h2></div></div>
@if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ $employment->exists?route('employments.update',$employment):route('employments.store') }}">@csrf @if($employment->exists)@method('PUT')@endif<section class="panel form-section"><div class="form-grid">
<div class="form-group"><label>Pekerja OKU</label><select class="select" name="oku_id" required>@foreach($okus as $oku)<option value="{{ $oku->id }}" @selected((int)$value('oku_id')===$oku->id)>{{ $oku->name }}</option>@endforeach</select></div>
<div class="form-group"><label>Majikan</label><select class="select" name="employer_id" required>@foreach($employers as $employer)<option value="{{ $employer->id }}" @selected((int)$value('employer_id')===$employer->id)>{{ $employer->company_name }}</option>@endforeach</select></div>
@foreach(['job_title'=>'Jawatan','department'=>'Jabatan','employment_type'=>'Jenis pekerjaan','supervisor_name'=>'Penyelia','salary'=>'Gaji'] as $field=>$label)<div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input class="field" id="{{ $field }}" name="{{ $field }}" value="{{ $field==='salary'?old('salary',$employment->salary_value):$value($field) }}" @if($field==='salary') type="number" step="0.01" @endif {{ in_array($field,['job_title','employment_type'])?'required':'' }}></div>@endforeach
@foreach(['start_date'=>'Tarikh mula','end_date'=>'Tarikh tamat'] as $field=>$label)<div class="form-group"><label for="{{ $field }}">{{ $label }}</label><input class="field" id="{{ $field }}" name="{{ $field }}" type="date" value="{{ old($field,$employment->{$field}?->format('Y-m-d')) }}" {{ $field==='start_date'?'required':'' }}></div>@endforeach
<div class="form-group"><label>Status</label><select class="select" name="status">@foreach(['PENDING','ACTIVE','INACTIVE','TERMINATED','REJECTED','UNDER_REVIEW'] as $status)<option @selected($value('status','PENDING')===$status)>{{ $status }}</option>@endforeach</select></div>
<div class="form-group"><label>Pengesahan</label><select class="select" name="verification_status">@foreach(['PENDING','VERIFIED','REJECTED','UNDER_REVIEW'] as $status)<option @selected($value('verification_status','PENDING')===$status)>{{ $status }}</option>@endforeach</select></div>
<div class="form-group full"><label>Catatan</label><textarea class="textarea" name="notes">{{ $value('notes') }}</textarea></div>
</div></section><div class="form-actions"><a class="btn" href="{{ route('employments.index') }}">Batal</a><button class="btn btn-primary">Simpan</button></div></form>
@endsection
