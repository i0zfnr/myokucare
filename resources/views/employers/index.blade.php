@extends('layout',['title'=>'Majikan'])
@section('content')
@php
    $sortUrl = function (string $column) use ($filters) {
        $direction = ($filters['sort_by'] ?? '') === $column && ($filters['sort_direction'] ?? 'asc') === 'asc' ? 'desc' : 'asc';
        return route('employers.index', array_merge(request()->query(), ['sort_by'=>$column,'sort_direction'=>$direction,'page'=>null]));
    };
@endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.rakan_pekerjaan.d378c2cc') }}</p><h2>{{ __('ui.senarai_majikan.5d042ca0') }}</h2><p>{{ __('ui.cari_dan_pantau_organisasi_yang_menawarkan_peluang.256c4baa') }}</p></div>
    @if(auth()->user()->hasRole('super_admin','jkm_officer'))<a class="btn btn-primary" href="{{ route('employers.create') }}">{{ __('ui.daftar_majikan.8a991b2d') }}</a>@endif
</div>

<section class="employer-stat-grid" aria-label="{{ __('ui.ringkasan_majikan.d9ee3590') }}">
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
    <div class="form-group employer-search"><label for="employer-search">{{ __('ui.cari_majikan.8c64540c') }}</label><input class="field" id="employer-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" maxlength="100" placeholder="{{ __('ui.nama_syarikat_nombor_pendaftaran_atau_pegawai.36cf1067') }}"></div>
    <div class="form-group"><label for="employer-sector">{{ __('ui.sektor.ee74fbed') }}</label><select class="select" id="employer-sector" name="sector"><option value="">{{ __('ui.semua_sektor.43439b1c') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}" @selected(($filters['sector']??'')===$sector)>{{ $sector }}</option>@endforeach</select></div>
    <div class="form-group"><label for="employer-status">{{ __('ui.status.bae7d5be') }}</label><select class="select" id="employer-status" name="status"><option value="">{{ __('ui.semua_status.baa2adda') }}</option><option value="active" @selected(($filters['status']??'')==='active')>{{ __('ui.aktif.89f29d42') }}</option><option value="inactive" @selected(($filters['status']??'')==='inactive')>{{ __('ui.tidak_aktif.c5f1e8e2') }}</option></select></div>
    <div class="form-group"><label for="employer-per-page">{{ __('ui.paparan.a61c4312') }}</label><select class="select" id="employer-per-page" name="per_page">@foreach([10,15,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??15)===$size)>{{ $size }} rekod</option>@endforeach</select></div>
    <button class="btn btn-primary" type="submit">{{ __('ui.cari.3f2275d7') }}</button>
    @if(request()->hasAny(['search','sector','status','per_page','sort_by']))<a class="btn" href="{{ route('employers.index') }}">{{ __('ui.tetapkan_semula.831ea874') }}</a>@endif
</form>

<div class="result-summary" role="status" aria-live="polite">
    <span>{{ __('ui.memaparkan.9a038a12') }} <strong>{{ $employers->firstItem()??0 }}–{{ $employers->lastItem()??0 }}</strong> {{ __('ui.daripada.4692369d') }} <strong>{{ $employers->total() }}</strong> {{ __('ui.majikan.3ff4a318') }}</span>
</div>

<section class="panel employer-table-panel">
<div class="table-wrap">
<table class="data-table employer-table" aria-label="{{ __('ui.senarai_majikan_berdaftar.9af79494') }}">
<thead><tr>
    @foreach(['company_name'=>'Syarikat','industry_sector'=>'Sektor'] as $column=>$label)
    <th scope="col" @if(($filters['sort_by']??'')===$column) aria-sort="{{ ($filters['sort_direction']??'asc')==='asc'?'ascending':'descending' }}" @endif><a href="{{ $sortUrl($column) }}">{{ $label }} <span aria-hidden="true">↕</span></a></th>
    @endforeach
    <th scope="col">{{ __('ui.pegawai_dihubungi.8f20a6ae') }}</th>
    <th scope="col" @if(($filters['sort_by']??'')==='jobs_count') aria-sort="{{ ($filters['sort_direction']??'asc')==='asc'?'ascending':'descending' }}" @endif><a href="{{ $sortUrl('jobs_count') }}">{{ __('ui.jawatan.065f2a70') }} <span aria-hidden="true">↕</span></a></th>
    <th scope="col">{{ __('ui.status.bae7d5be') }}</th><th scope="col"><span class="sr-only">{{ __('ui.tindakan.4c20e744') }}</span></th>
</tr></thead>
<tbody>
@forelse($employers as $employer)
<tr>
    <td data-label="Syarikat"><strong class="employer-name">{{ $employer->company_name }}</strong><small>{{ $employer->registration_number }}</small></td>
    <td data-label="Sektor">{{ $employer->industry_sector }}</td>
    <td data-label="Pegawai"><strong>{{ $employer->contact_person }}</strong><small><a href="mailto:{{ $employer->email }}">{{ $employer->email }}</a> · <a href="tel:{{ preg_replace('/\s+/','',$employer->phone_number) }}">{{ $employer->phone_number }}</a></small></td>
    <td data-label="Jawatan"><strong>{{ $employer->active_jobs_count }}</strong> {{ __('ui.aktif.742b53b7') }} <small>{{ $employer->jobs_count }} keseluruhan</small></td>
    <td data-label="Status"><span class="status-badge {{ $employer->is_active?'is-active':'is-inactive' }}"><span aria-hidden="true"></span>{{ $employer->is_active?'Aktif':'Tidak aktif' }}</span>@if($employer->has_oku_quota)<small class="oku-friendly">{{ __('ui.mesra_oku.88104670') }}</small>@endif</td>
    <td data-label="Tindakan"><div class="table-actions">@if(auth()->user()->hasRole('super_admin','jkm_officer'))<a href="{{ route('employers.edit',$employer) }}" aria-label="Sunting {{ $employer->company_name }}">Sunting</a>@endif @if($employer->website)<a href="{{ $employer->website }}" target="_blank" rel="noopener noreferrer" aria-label="Buka laman web {{ $employer->company_name }}">Laman web ↗</a>@else<a href="mailto:{{ $employer->email }}" aria-label="E-mel {{ $employer->company_name }}">Hubungi</a>@endif</div></td>
</tr>
@empty
<tr><td class="empty employer-empty" colspan="6"><span aria-hidden="true">⌕</span><strong>{{ __('ui.tiada_majikan_ditemui.6505357a') }}</strong><p>{{ __('ui.cuba_ubah_kata_carian_atau_penapis.ef842597') }}</p>@if(request()->query())<a class="btn" href="{{ route('employers.index') }}">{{ __('ui.kosongkan_penapis.0ec32ab4') }}</a>@endif</td></tr>
@endforelse
</tbody></table>
</div></section>
<div class="pagination">{{ $employers->links() }}</div>
@endsection
