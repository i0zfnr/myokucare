@extends('layout',['title'=>'Laporan Kebajikan'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Analitik</p><h2>Statistik Kebajikan</h2><p>Ringkasan permohonan berdasarkan status semasa.</p></div><a class="btn btn-primary" href="{{ route('reports.export','welfare') }}">Muat Turun CSV</a></div>
<section class="metric-grid">@forelse($stats as $status=>$total)<article class="metric-card"><div class="metric-top"><span class="metric-icon">♡</span></div><span>{{ $status }}</span><strong>{{ number_format($total) }}</strong></article>@empty<article class="panel empty">Belum ada data permohonan kebajikan.</article>@endforelse</section>
@endsection
