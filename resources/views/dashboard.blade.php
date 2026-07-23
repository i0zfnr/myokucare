@extends('layout',['title'=>'Dashboard'])
@section('content')
@php
    $user = auth()->user();
    $canManageOku = $user->hasRole('super_admin', 'jkm_officer');
    $canViewJobs = $user->hasRole('super_admin', 'jkm_officer', 'employer', 'oku_user', 'family_member');
    $canViewWelfare = $user->hasRole('super_admin', 'jkm_officer', 'oku_user', 'family_member');
    $canViewReports = $user->hasRole('super_admin', 'jkm_officer', 'viewer');
    $metrics=[['Jumlah OKU',$stats['total'],'◉'],['OKU Aktif',$stats['active'],'✓'],['Sedang Bekerja',$stats['employed'],'◆'],['Permohonan Kebajikan',$stats['pending_welfare'],'♡']];
@endphp
<div class="page-head"><div><p class="eyebrow">Ringkasan Sistem</p><h2>Selamat datang ke MyOKUcare</h2><p>Pantau rekod OKU, peluang pekerjaan dan permohonan kebajikan.</p></div>@if($canManageOku)<a class="btn btn-primary" href="{{ route('oku.create') }}">＋ Daftar OKU Baharu</a>@endif</div>
<section class="metric-grid" aria-label="Statistik utama">@foreach($metrics as [$label,$value,$icon])<article class="metric-card"><div class="metric-top"><span class="metric-icon" aria-hidden="true">{{ $icon }}</span><span class="metric-change">Dikemas kini</span></div><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>@endforeach</section>
<section class="dashboard-grid">
    <article class="panel"><div class="panel-head"><div><h3>Taburan Kategori OKU</h3><p>Perbandingan rekod aktif mengikut kategori</p></div><span class="badge">Data semasa</span></div><div class="chart">@forelse($stats['categories'] as $category=>$total)<div class="bar-group"><span class="bar" style="height:{{ max(12,min(100,$total*12)) }}%"></span><span class="bar alt" style="height:{{ max(8,min(78,$total*8)) }}%"></span><label>{{ $category }}</label></div>@empty @foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $i=>$category)<div class="bar-group"><span class="bar" style="height:{{ [46,68,38,82,55][$i] }}%"></span><span class="bar alt" style="height:{{ [30,44,25,60,36][$i] }}%"></span><label>{{ $category }}</label></div>@endforeach @endforelse</div></article>
    <aside class="panel"><div class="panel-head"><div><h3>Tindakan Pantas</h3><p>Akses modul yang kerap digunakan</p></div></div><div class="quick-list">@if($canManageOku)<a class="quick-link" href="{{ route('oku.create') }}"><span class="metric-icon" aria-hidden="true">＋</span><span><strong>Daftar OKU</strong><span>Tambah rekod individu baharu</span></span></a>@endif @if($canViewJobs)<a class="quick-link" href="{{ route('jobs.index') }}"><span class="metric-icon" aria-hidden="true">◆</span><span><strong>Peluang Kerja</strong><span>Semak senarai jawatan</span></span></a>@endif @if($canViewWelfare)<a class="quick-link" href="{{ route('welfare.index') }}"><span class="metric-icon" aria-hidden="true">♡</span><span><strong>Permohonan Kebajikan</strong><span>Pantau status permohonan</span></span></a>@endif @if($canViewReports)<a class="quick-link" href="{{ route('reports.employment') }}"><span class="metric-icon" aria-hidden="true">↗</span><span><strong>Laporan</strong><span>Lihat statistik pekerjaan</span></span></a>@endif</div></aside>
</section>
@endsection
