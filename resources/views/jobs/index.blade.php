@extends('layout',['title'=>'Peluang Kerja'])
@section('content')
<div class="page-head">
    <div><p class="eyebrow">Pekerjaan Inklusif</p><h2>Senarai Peluang Kerja</h2><p>Cari jawatan aktif yang sepadan dengan keperluan dan lokasi anda.</p></div>
    @if(auth()->user()->hasRole('super_admin','jkm_officer','employer'))<a class="btn btn-primary" href="{{ route('jobs.create') }}">Tambah Jawatan</a>@endif
</div>

<section class="job-summary" aria-label="Ringkasan peluang kerja">
    <div><strong>{{ number_format($stats['available']) }}</strong><span>Jawatan tersedia</span></div>
    <div><strong>{{ number_format($stats['employers']) }}</strong><span>Majikan aktif</span></div>
    <div><strong>{{ number_format($stats['locations']) }}</strong><span>Lokasi ditawarkan</span></div>
</section>

<form class="panel job-filter-panel" method="get" action="{{ route('jobs.index') }}" role="search">
    <div class="form-group job-filter-search"><label for="job-search">Cari jawatan</label><input class="field" id="job-search" name="search" type="search" value="{{ $filters['search']??'' }}" maxlength="100" placeholder="Jawatan, kemahiran atau nama majikan"></div>
    <div class="form-group"><label for="job-category">Kategori OKU</label><select class="select" id="job-category" name="category"><option value="">Semua kategori</option>@foreach(['Semua','Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $option)<option value="{{ $option }}" @selected(($filters['category']??'')===$option)>{{ $option }}</option>@endforeach</select></div>
    <div class="form-group"><label for="job-location">Lokasi</label><select class="select" id="job-location" name="location"><option value="">Semua lokasi</option>@foreach($locations as $location)<option value="{{ $location }}" @selected(($filters['location']??'')===$location)>{{ $location }}</option>@endforeach</select></div>
    <div class="form-group"><label for="employment-type">Jenis pekerjaan</label><select class="select" id="employment-type" name="employment_type"><option value="">Semua jenis</option>@foreach(['Sepenuh Masa','Separuh Masa','Kontrak','Sementara'] as $option)<option value="{{ $option }}" @selected(($filters['employment_type']??'')===$option)>{{ $option }}</option>@endforeach</select></div>
    <div class="form-group"><label for="salary-min">Gaji minimum</label><input class="field" id="salary-min" name="salary_min" type="number" min="0" step="100" value="{{ $filters['salary_min']??'' }}" placeholder="RM"></div>
    <div class="form-group"><label for="job-sort">Susunan</label><select class="select" id="job-sort" name="sort_by"><option value="created_at" @selected(($filters['sort_by']??'created_at')==='created_at')>Terbaharu</option><option value="salary_min" @selected(($filters['sort_by']??'')==='salary_min')>Gaji</option><option value="application_deadline" @selected(($filters['sort_by']??'')==='application_deadline')>Tarikh tutup</option><option value="title" @selected(($filters['sort_by']??'')==='title')>Nama jawatan</option></select></div>
    <input type="hidden" name="sort_direction" value="{{ ($filters['sort_by']??'created_at')==='title'?'asc':'desc' }}">
    <div class="job-filter-actions"><button class="btn btn-primary" type="submit">Cari Kerja</button>@if(request()->query())<a class="btn" href="{{ route('jobs.index') }}">Kosongkan</a>@endif</div>
</form>

<div class="result-summary" role="status" aria-live="polite"><span><strong>{{ $jobs->total() }}</strong> peluang kerja ditemui</span></div>

<div class="job-directory">
@forelse($jobs as $job)
@php $descriptionId='job-description-'.$job->id; $interested=in_array($job->id,$interestedJobIds,true); @endphp
<article class="panel opportunity-card" aria-labelledby="job-title-{{ $job->id }}" aria-describedby="{{ $descriptionId }}">
    <div class="opportunity-top"><span class="company-initial" aria-hidden="true">{{ mb_substr($job->employer->company_name,0,1) }}</span><div><p>{{ $job->employer->company_name }}</p><h3 id="job-title-{{ $job->id }}">{{ $job->title }}</h3></div><span class="job-category">{{ $job->oku_category_suitable }}</span></div>
    <div class="job-meta"><span>⌖ {{ $job->location }}</span><span>▣ {{ $job->employment_type }}</span><span>RM {{ number_format((float)$job->salary_min,0) }}{{ $job->salary_max?' – '.number_format((float)$job->salary_max,0):'+' }}</span></div>
    <p class="job-description" id="{{ $descriptionId }}">{{ \Illuminate\Support\Str::limit($job->description,150) }}</p>
    <details class="job-details"><summary>Lihat keperluan jawatan</summary><div><h4>Keperluan</h4><p>{{ $job->requirements }}</p>@if($job->responsibilities)<h4>Tanggungjawab</h4><p>{{ $job->responsibilities }}</p>@endif</div></details>
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
            <a class="btn btn-primary" href="{{ route('career-profile.show') }}">Lengkapkan Profil</a>
            @endif
        @else
        <div class="job-management-actions"><span class="applications-count">{{ number_format($job->applications_count) }} minat</span>@if(auth()->user()->role!=='employer' || (int)auth()->user()->employer_id===(int)$job->employer_id)<a class="btn" href="{{ route('jobs.edit',$job) }}">Sunting</a>@endif</div>
        @endif
    </div>
</article>
@empty
<section class="panel job-empty"><span aria-hidden="true">⌕</span><h3>Tiada peluang kerja ditemui</h3><p>Cuba ubah carian, kategori atau lokasi anda.</p>@if(request()->query())<a class="btn" href="{{ route('jobs.index') }}">Kosongkan penapis</a>@endif</section>
@endforelse
</div>
<div class="pagination">{{ $jobs->links() }}</div>
@endsection
