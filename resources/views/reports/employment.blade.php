@extends('layout',['title'=>'Laporan Pekerjaan'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Analitik</p><h2>Statistik Pekerjaan</h2><p>Ringkasan status pekerjaan komuniti OKU berdaftar.</p></div><a class="btn btn-primary" href="{{ route('reports.export','oku') }}">Muat Turun CSV</a></div>
<section class="metric-grid">@foreach([['Jumlah OKU',$stats['total'],'◉'],['Bekerja',$stats['employed'],'✓'],['Belum Bekerja',$stats['unemployed'],'◆'],['Pekerjaan Aktif',$stats['active_employments'],'↗']] as [$label,$value,$icon])<article class="metric-card"><div class="metric-top"><span class="metric-icon">{{ $icon }}</span></div><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>@endforeach</section>
@endsection
