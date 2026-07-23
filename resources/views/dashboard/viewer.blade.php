@extends('layout',['title'=>'Dashboard Viewer'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Analitik Read-Only</p><h2>Ringkasan MyOKUcare</h2><p>Lihat statistik agregat tanpa akses kepada pengurusan rekod peribadi.</p></div><a class="btn btn-primary" href="{{ route('reports.employment') }}">Lihat Laporan</a></div>
@php $metrics=[['Jumlah OKU',$stats['total'],'◉'],['Kadar Bekerja',$stats['total'] ? round(($stats['employed']/$stats['total'])*100) : 0,'%'],['Majikan Aktif',$totalEmployers,'▣'],['Jawatan Terbuka',$openJobs,'◆']]; @endphp
@php $statKeys=['total','employment_rate','total_employers','open_jobs']; @endphp
<section class="metric-grid" data-live-dashboard data-statistics-url="{{ route('dashboard.statistics') }}">@foreach($metrics as $index=>[$label,$value,$icon])<article class="metric-card"><div class="metric-top"><span class="metric-icon">{{ $icon }}</span><span class="metric-change">Agregat</span></div><span>{{ $label }}</span><strong data-stat="{{ $statKeys[$index] }}" @if($icon==='%') data-suffix="%" @endif>{{ number_format($value) }}{{ $icon==='%'?'%':'' }}</strong></article>@endforeach</section>
<section class="dashboard-grid">
    @include('dashboard.partials.live-oku-statistics', ['embedded' => true])
    <aside class="panel"><div class="panel-head"><div><h3>Laporan Tersedia</h3><p>Akses statistik tanpa mengubah data</p></div></div><div class="quick-list"><a class="quick-link" href="{{ route('reports.employment') }}"><span class="metric-icon">↗</span><span><strong>Laporan Pekerjaan</strong><span>Statistik status pekerjaan</span></span></a><a class="quick-link" href="{{ route('reports.welfare') }}"><span class="metric-icon">≋</span><span><strong>Laporan Kebajikan</strong><span>Statistik permohonan</span></span></a></div></aside>
</section>
@endsection
