@extends('layout',['title'=>'Dashboard Majikan'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Portal Majikan</p><h2>{{ $employer?->company_name ?? 'Selamat datang, Majikan' }}</h2><p>Pantau jawatan, permohonan calon dan hasil pengambilan pekerja.</p></div><a class="btn btn-primary" href="{{ route('jobs.index') }}">Lihat Peluang Kerja</a></div>
@php $metrics=[['Jumlah Jawatan',$totalJobs,'◆'],['Jawatan Aktif',$activeJobs,'✓'],['Jumlah Permohonan',$applications,'◎'],['Calon Diambil',$hired,'★']]; @endphp
<section class="metric-grid">@foreach($metrics as [$label,$value,$icon])<article class="metric-card"><div class="metric-top"><span class="metric-icon">{{ $icon }}</span><span class="metric-change">Syarikat Anda</span></div><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>@endforeach</section>
<section class="dashboard-grid">
    <article class="panel"><div class="panel-head"><div><h3>Jawatan Terkini</h3><p>Prestasi iklan pekerjaan syarikat anda</p></div></div><div class="activity-list">@forelse($jobs as $job)<div class="activity-row"><span class="metric-icon">◆</span><div><strong>{{ $job->title }}</strong><span>{{ $job->location }} · {{ $job->employment_type }}</span></div><b>{{ $job->job_interests_count }} calon</b></div>@empty<div class="empty">{{ $employer ? 'Belum ada jawatan diterbitkan.' : 'Akaun ini belum dipautkan kepada profil majikan.' }}</div>@endforelse</div></article>
    <aside class="panel"><div class="panel-head"><div><h3>Tindakan Majikan</h3><p>Urus operasi pengambilan</p></div></div><div class="quick-list"><a class="quick-link" href="{{ route('jobs.index') }}"><span class="metric-icon">◆</span><span><strong>Senarai Jawatan</strong><span>Semak jawatan tersedia</span></span></a><a class="quick-link" href="{{ route('employers.index') }}"><span class="metric-icon">▣</span><span><strong>Profil Syarikat</strong><span>Lihat maklumat majikan</span></span></a></div></aside>
</section>
@endsection
