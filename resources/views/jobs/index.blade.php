@extends('layout',['title'=>'Peluang Kerja'])
@section('content')
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.pekerjaan_inklusif.c138085d') }}</p><h2>{{ __('ui.senarai_peluang_kerja.3906e2bc') }}</h2><p>{{ __('ui.cari_jawatan_aktif_yang_sepadan_dengan_keperluan.8dbf6eaa') }}</p></div>
    @if(auth()->user()->hasRole('super_admin','jkm_officer','employer'))<a class="btn btn-primary" href="{{ route('jobs.create') }}">{{ __('ui.tambah_jawatan.73baee5c') }}</a>@endif
</div>

<section class="job-summary" aria-label="{{ __('ui.ringkasan_peluang_kerja.6d3654ff') }}">
    <div><strong>{{ number_format($stats['available']) }}</strong><span>{{ __('ui.jawatan_tersedia.fcf93c9d') }}</span></div>
    <div><strong>{{ number_format($stats['employers']) }}</strong><span>{{ __('ui.majikan_aktif.abf56e2f') }}</span></div>
    <div><strong>{{ number_format($stats['locations']) }}</strong><span>{{ __('ui.lokasi_ditawarkan.dc85fecf') }}</span></div>
</section>

<form class="panel job-filter-panel" method="get" action="{{ route('jobs.index') }}" role="search">
    <div class="form-group job-filter-search"><label for="job-search">{{ __('ui.cari_jawatan.e1e64a9b') }}</label><input class="field" id="job-search" name="search" type="search" value="{{ $filters['search']??'' }}" maxlength="100" placeholder="{{ __('ui.jawatan_kemahiran_atau_nama_majikan.5e2d136c') }}"></div>
    <div class="form-group"><label for="job-category">{{ __('ui.kategori_oku.5a4ba70d') }}</label><select class="select" id="job-category" name="category"><option value="">{{ __('ui.semua_kategori.3ee43aaf') }}</option>@foreach(['Semua','Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $option)<option value="{{ $option }}" @selected(($filters['category']??'')===$option)>{{ $option }}</option>@endforeach</select></div>
    <div class="form-group"><label for="job-location">{{ __('ui.lokasi.7e1ab5d9') }}</label><select class="select" id="job-location" name="location"><option value="">{{ __('ui.semua_lokasi.edfd39e7') }}</option>@foreach($locations as $location)<option value="{{ $location }}" @selected(($filters['location']??'')===$location)>{{ $location }}</option>@endforeach</select></div>
    <div class="form-group"><label for="employment-type">{{ __('ui.jenis_pekerjaan.530078bd') }}</label><select class="select" id="employment-type" name="employment_type"><option value="">{{ __('ui.semua_jenis.dbf8ef8a') }}</option>@foreach(['Sepenuh Masa','Separuh Masa','Kontrak','Sementara'] as $option)<option value="{{ $option }}" @selected(($filters['employment_type']??'')===$option)>{{ $option }}</option>@endforeach</select></div>
    <div class="form-group"><label for="salary-min">{{ __('ui.gaji_minimum.d75f0618') }}</label><input class="field" id="salary-min" name="salary_min" type="number" min="0" step="100" value="{{ $filters['salary_min']??'' }}" placeholder="RM"></div>
    <div class="form-group"><label for="job-sort">{{ __('ui.susunan.cbe00edc') }}</label><select class="select" id="job-sort" name="sort_by"><option value="created_at" @selected(($filters['sort_by']??'created_at')==='created_at')>{{ __('ui.terbaharu.6dfac853') }}</option><option value="salary_min" @selected(($filters['sort_by']??'')==='salary_min')>{{ __('ui.gaji.78f77057') }}</option><option value="application_deadline" @selected(($filters['sort_by']??'')==='application_deadline')>{{ __('ui.tarikh_tutup.ee57a073') }}</option><option value="title" @selected(($filters['sort_by']??'')==='title')>{{ __('ui.nama_jawatan.bfe039a3') }}</option></select></div>
    <input type="hidden" name="sort_direction" value="{{ ($filters['sort_by']??'created_at')==='title'?'asc':'desc' }}">
    <div class="job-filter-actions"><button class="btn btn-primary" type="submit">{{ __('ui.cari_kerja.1f4af78d') }}</button>@if(request()->query())<a class="btn" href="{{ route('jobs.index') }}">{{ __('ui.kosongkan.899f41b5') }}</a>@endif</div>
</form>

<div class="result-summary" role="status" aria-live="polite"><span><strong>{{ $jobs->total() }}</strong> {{ __('ui.peluang_kerja_ditemui.f66650c3') }}</span></div>

<div class="job-directory">
@forelse($jobs as $job)
@php $descriptionId='job-description-'.$job->id; $interested=in_array($job->id,$interestedJobIds,true); @endphp
<article class="panel opportunity-card" aria-labelledby="job-title-{{ $job->id }}" aria-describedby="{{ $descriptionId }}">
    <div class="opportunity-top"><span class="company-initial" aria-hidden="true">{{ mb_substr($job->employer->company_name,0,1) }}</span><div><p>{{ $job->employer->company_name }}</p><h3 id="job-title-{{ $job->id }}">{{ $job->title }}</h3></div><span class="job-category">{{ $job->oku_category_suitable }}</span></div>
    <div class="job-meta"><span>⌖ {{ $job->location }}</span><span>▣ {{ $job->employment_type }}</span><span>RM {{ number_format((float)$job->salary_min,0) }}{{ $job->salary_max?' – '.number_format((float)$job->salary_max,0):'+' }}</span></div>
    <p class="job-description" id="{{ $descriptionId }}">{{ \Illuminate\Support\Str::limit($job->description,150) }}</p>
    <details class="job-details"><summary>{{ __('ui.lihat_keperluan_jawatan.49eab7b5') }}</summary><div><h4>{{ __('ui.keperluan.3dfa39a3') }}</h4><p>{{ $job->requirements }}</p>@if($job->responsibilities)<h4>{{ __('ui.tanggungjawab.9a45d2bc') }}</h4><p>{{ $job->responsibilities }}</p>@endif</div></details>
    <div class="opportunity-footer">
        <span class="job-deadline {{ $job->application_deadline?->diffInDays(today(),false)>=-7?'is-soon':'' }}">
            @if($job->application_deadline)
                Tarikh tutup: <strong>{{ $job->application_deadline->format('d/m/Y') }}</strong>
            @else
                Permohonan dibuka
            @endif
        </span>
        @if(auth()->user()->role==='oku_user')
            @if($canApply)
            <form method="post" action="{{ route('jobs.interest',$job) }}">@csrf<button class="btn {{ $interested?'':'btn-primary' }}" type="submit" @disabled($interested)>{{ $interested?'Minat Direkodkan':'Saya Berminat' }}</button></form>
            @else
            <a class="btn btn-primary" href="{{ route('career-profile.show') }}">{{ __('ui.lengkapkan_profil.3dac4b06') }}</a>
            @endif
        @else
        <div class="job-management-actions"><span class="applications-count">{{ number_format($job->applications_count) }} minat</span>@if(auth()->user()->role!=='employer' || (int)auth()->user()->employer_id===(int)$job->employer_id)<a class="btn" href="{{ route('jobs.edit',$job) }}">{{ __('ui.sunting.b7b0d4ed') }}</a>@endif</div>
        @endif
    </div>
</article>
@empty
<section class="panel job-empty"><span aria-hidden="true">⌕</span><h3>{{ __('ui.tiada_peluang_kerja_ditemui.d66c4a11') }}</h3><p>{{ __('ui.cuba_ubah_carian_kategori_atau_lokasi_anda.2645a0dd') }}</p>@if(request()->query())<a class="btn" href="{{ route('jobs.index') }}">{{ __('ui.kosongkan_penapis.0ec32ab4') }}</a>@endif</section>
@endforelse
</div>
<div class="pagination">{{ $jobs->links() }}</div>
@endsection
