@extends('layout',['title'=>'Majikan'])
@section('content')
@php
    $sortUrl = function (string $column) use ($filters) {
        $direction = ($filters['sort_by'] ?? '') === $column && ($filters['sort_direction'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
        return route('employers.index', array_merge(request()->query(), ['sort_by'=>$column,'sort_direction'=>$direction,'page'=>null]));
    };
@endphp
<div class="page-head">
    <div><p class="eyebrow">Rakan Pekerjaan</p><h2>Senarai Majikan</h2><p>Cari dan pantau organisasi yang menawarkan peluang pekerjaan inklusif.</p></div>
    @if(auth()->user()->hasRole('super_admin','jkm_officer'))<a class="btn btn-primary" href="{{ route('employers.create') }}">Daftar Majikan</a>@endif
</div>

<section class="employer-stat-grid" aria-label="Ringkasan majikan">
@foreach([
    ['Majikan berdaftar',$stats['total'],'Semua organisasi','company'],
    ['Majikan aktif',$stats['active'],'Profil aktif','active'],
    ['Mesra OKU',$stats['oku_friendly'],'Mempunyai kuota OKU','inclusive'],
    ['Jawatan aktif',$stats['active_jobs'],'Peluang sedang dibuka','jobs'],
] as [$label,$number,$caption,$tone])
<article class="panel employer-stat {{ $tone }}"><span>{{ $label }}</span><strong>{{ number_format($number) }}</strong><small>{{ $caption }}</small></article>
@endforeach
</section>

<form class="panel employer-filter" method="get" action="{{ route('employers.index') }}" role="search">
    <div class="form-group employer-search"><label for="employer-search">Cari majikan</label><input class="field" id="employer-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="Nama syarikat, nombor pendaftaran atau pegawai"></div>
    <div class="form-group"><label for="employer-sector">Sektor</label><select class="select" id="employer-sector" name="sector"><option value="">Semua sektor</option>@foreach($sectors as $sector)<option value="{{ $sector }}" @selected(($filters['sector']??'')===$sector)>{{ $sector }}</option>@endforeach</select></div>
    <div class="form-group"><label for="employer-status">Status</label><select class="select" id="employer-status" name="status"><option value="">Semua status</option><option value="active" @selected(($filters['status']??'')==='active')>Aktif</option><option value="inactive" @selected(($filters['status']??'')==='inactive')>Tidak aktif</option></select></div>
    <div class="form-group"><label for="employer-per-page">Paparan</label><select class="select" id="employer-per-page" name="per_page">@foreach([10,15,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??15)===$size)>{{ $size }} rekod</option>@endforeach</select></div>
    <button class="btn btn-primary" type="submit">Cari</button>
    @if(request()->hasAny(['search','sector','status','per_page','sort_by']))<a class="btn" href="{{ route('employers.index') }}">Tetapkan semula</a>@endif
</form>

<div class="result-summary" role="status" aria-live="polite">
    <span>Memaparkan <strong>{{ $employers->firstItem()??0 }}–{{ $employers->lastItem()??0 }}</strong> daripada <strong>{{ $employers->total() }}</strong> majikan</span>
</div>

<section class="panel employer-table-panel">
<div class="table-wrap">
<table class="data-table employer-table" aria-label="Senarai majikan berdaftar">
<thead><tr>
    @foreach(['company_name'=>'Syarikat','industry_sector'=>'Sektor'] as $column=>$label)
    <th scope="col" @if(($filters['sort_by']??'')===$column) aria-sort="{{ ($filters['sort_direction']??'asc')==='asc'?'ascending':'descending' }}" @endif><a href="{{ $sortUrl($column) }}">{{ $label }} <span aria-hidden="true">↕</span></a></th>
    @endforeach
    <th scope="col">Pegawai dihubungi</th>
    <th scope="col" @if(($filters['sort_by']??'')==='jobs_count') aria-sort="{{ ($filters['sort_direction']??'asc')==='asc'?'ascending':'descending' }}" @endif><a href="{{ $sortUrl('jobs_count') }}">Jawatan <span aria-hidden="true">↕</span></a></th>
    <th scope="col">Status</th><th scope="col"><span class="sr-only">Tindakan</span></th>
</tr></thead>
<tbody>
@forelse($employers as $employer)
<tr>
    <td data-label="Syarikat"><strong class="employer-name">{{ $employer->company_name }}</strong><small>{{ $employer->registration_number }}</small></td>
    <td data-label="Sektor">{{ $employer->industry_sector }}</td>
    <td data-label="Pegawai"><strong>{{ $employer->contact_person }}</strong><small><a href="mailto:{{ $employer->email }}">{{ $employer->email }}</a> · <a href="tel:{{ preg_replace('/\s+/','',$employer->phone_number) }}">{{ $employer->phone_number }}</a></small></td>
    <td data-label="Jawatan"><strong>{{ $employer->active_jobs_count }}</strong> aktif <small>{{ $employer->jobs_count }} keseluruhan</small></td>
    <td data-label="Status"><span class="status-badge {{ $employer->is_active?'is-active':'is-inactive' }}"><span aria-hidden="true"></span>{{ $employer->is_active?'Aktif':'Tidak aktif' }}</span>@if($employer->has_oku_quota)<small class="oku-friendly">Mesra OKU</small>@endif</td>
    <td data-label="Tindakan"><div class="table-actions">@if(auth()->user()->hasRole('super_admin','jkm_officer'))<a href="{{ route('employers.edit',$employer) }}" aria-label="Sunting {{ $employer->company_name }}">Sunting</a>@endif @if($employer->website)<a href="{{ $employer->website }}" target="_blank" rel="noopener noreferrer" aria-label="Buka laman web {{ $employer->company_name }}">Laman web ↗</a>@else<a href="mailto:{{ $employer->email }}" aria-label="E-mel {{ $employer->company_name }}">Hubungi</a>@endif</div></td>
</tr>
@empty
<tr><td class="empty employer-empty" colspan="6"><span aria-hidden="true">⌕</span><strong>Tiada majikan ditemui</strong><p>Cuba ubah kata carian atau penapis.</p>@if(request()->query())<a class="btn" href="{{ route('employers.index') }}">Kosongkan penapis</a>@endif</td></tr>
@endforelse
</tbody></table>
</div></section>
<div class="pagination">{{ $employers->links() }}</div>
@endsection
