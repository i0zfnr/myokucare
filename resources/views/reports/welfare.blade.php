@extends('layout',['title'=>'Laporan Kebajikan'])
@section('content')
@php
    $summary=$report['summary'];
    $maxType=max(1,$report['types']->max('total')??0);
    $maxMonth=max(1,$report['monthly']->max('total'));
    $statusLabels=['Pending'=>'Menunggu','Under Review'=>'Dalam Semakan','Approved'=>'Diluluskan','Rejected'=>'Ditolak'];
@endphp
<div class="page-head">
    <div><p class="eyebrow">Analitik Kebajikan</p><h2>Statistik Kebajikan OKU</h2><p>Analisis agregat beban kes, keputusan dan keperluan bantuan kebajikan.</p></div>
    <div class="page-actions report-actions"><button class="btn" type="button" data-print-report>Cetak / Simpan PDF</button><a class="btn btn-primary" href="{{ route('reports.welfare-export',$filters) }}">Eksport Statistik CSV</a></div>
</div>

<form class="panel report-filter-panel welfare-report-filter" method="get" action="{{ route('reports.welfare') }}" aria-label="Tapis laporan kebajikan">
    <div class="form-group"><label for="welfare-report-from">Tarikh mula</label><input class="field" id="welfare-report-from" name="date_from" type="date" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="welfare-report-to">Tarikh akhir</label><input class="field" id="welfare-report-to" name="date_to" type="date" value="{{ $filters['date_to']??'' }}"></div>
    <div class="form-group"><label for="welfare-report-status">Status</label><select class="select" id="welfare-report-status" name="status"><option value="">Semua status</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="form-group"><label for="welfare-report-type">Jenis bantuan</label><select class="select" id="welfare-report-type" name="type"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type }}" @selected(($filters['type']??'')===$type)>{{ $type }}</option>@endforeach</select></div>
    <div class="form-group"><label for="welfare-report-category">Kategori OKU</label><select class="select" id="welfare-report-category" name="category"><option value="">Semua kategori</option>@foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $category)<option value="{{ $category }}" @selected(($filters['category']??'')===$category)>{{ $category }}</option>@endforeach</select></div>
    <div class="report-filter-actions"><button class="btn btn-primary" type="submit">Jana Laporan</button>@if(request()->query())<a class="btn" href="{{ route('reports.welfare') }}">Tetapkan Semula</a>@endif</div>
</form>

<div class="report-scope" aria-label="Skop laporan">
    <strong>Skop laporan:</strong>
    <span>{{ $filters['date_from']??'Semua tarikh' }} hingga {{ $filters['date_to']??'semasa' }}</span>
    @if(filled($filters['status']??null))<span>Status: {{ $statusLabels[$filters['status']] }}</span>@endif
    @if(filled($filters['type']??null))<span>Jenis: {{ $filters['type'] }}</span>@endif
    @if(filled($filters['category']??null))<span>Kategori: {{ $filters['category'] }}</span>@endif
</div>

<section class="report-metric-grid welfare-report-metrics" aria-label="Ringkasan statistik kebajikan" role="status">
@foreach([
    ['Jumlah Permohonan',$summary['total'],'Semua rekod dalam skop','total'],
    ['Menunggu',$summary['pending'],'Belum diproses','pending'],
    ['Dalam Semakan',$summary['under_review'],'Sedang diurus','review'],
    ['Diluluskan',$summary['approved'],'Keputusan positif','approved'],
    ['Kadar Kelulusan',$summary['approval_rate'].'%','Daripada kes diputuskan','rate'],
] as [$label,$value,$caption,$tone])
<article class="panel report-metric welfare-{{ $tone }}" aria-label="{{ $label }}: {{ $value }}"><span>{{ $label }}</span><strong>{{ is_numeric($value)?number_format($value):$value }}</strong><small>{{ $caption }}</small></article>
@endforeach
</section>

<div class="report-grid welfare-report-grid">
<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">Keperluan Bantuan</p><h3>Permohonan Mengikut Jenis</h3><p>Jenis bantuan yang paling kerap dimohon.</p></div></div>
    @if($report['types']->isNotEmpty())
    <div class="welfare-type-list">
    @foreach($report['types'] as $type=>$data)
        <div><div><strong>{{ $type }}</strong><span>{{ $data['approved'] }} diluluskan</span></div><div class="report-bar"><span style="width:{{ ($data['total']/$maxType)*100 }}%"></span></div><b>{{ $data['total'] }}</b></div>
    @endforeach
    </div>
    @else
    <div class="report-empty">Belum ada permohonan dalam skop laporan ini.</div>
    @endif
</section>

<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">Keputusan Kes</p><h3>Taburan Status</h3><p>Komposisi status permohonan semasa.</p></div></div>
    @php
        $pendingPercent=$summary['total']?round(($summary['pending']/$summary['total'])*100,1):0;
        $reviewPercent=$summary['total']?round(($summary['under_review']/$summary['total'])*100,1):0;
        $approvedPercent=$summary['total']?round(($summary['approved']/$summary['total'])*100,1):0;
    @endphp
    <div class="report-donut-wrap">
        <div class="report-donut welfare-donut" style="--pending:{{ $pendingPercent }}%;--review:{{ $pendingPercent+$reviewPercent }}%;--approved:{{ $pendingPercent+$reviewPercent+$approvedPercent }}%" role="img" aria-label="Taburan status permohonan"><span><strong>{{ $summary['total'] }}</strong>Permohonan</span></div>
        <ul class="welfare-status-legend">@foreach($report['statuses'] as $status=>$total)<li class="{{ str($status)->slug() }}"><span></span>{{ $statusLabels[$status] }} <b>{{ $total }}</b></li>@endforeach</ul>
    </div>
</section>

<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">Pemantauan Operasi</p><h3>Kecekapan Pemprosesan</h3><p>Petunjuk untuk tindakan dan jadual semakan JKM.</p></div></div>
    <div class="processing-metrics"><div><span>Purata masa keputusan</span><strong>{{ $summary['average_processing_days'] }}</strong><small>hari</small></div><div class="{{ $summary['overdue_reviews']?'needs-attention':'' }}"><span>Semakan telah lewat</span><strong>{{ $summary['overdue_reviews'] }}</strong><small>kes</small></div><div><span>Kes telah diputuskan</span><strong>{{ $summary['approved']+$summary['rejected'] }}</strong><small>kes</small></div></div>
</section>

<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">Enam Bulan</p><h3>Trend Permohonan</h3><p>Jumlah permohonan dan kelulusan bulanan.</p></div></div>
    <div class="welfare-trend" role="img" aria-label="Trend permohonan kebajikan enam bulan">@foreach($report['monthly'] as $month)<div><div><span class="total" style="height:{{ max(5,($month['total']/$maxMonth)*100) }}%"><b>{{ $month['total'] }}</b></span><span class="approved" style="height:{{ max(3,($month['approved']/$maxMonth)*100) }}%"><b>{{ $month['approved'] }}</b></span></div><small>{{ $month['label'] }}</small></div>@endforeach</div>
    <div class="trend-legend"><span class="total"></span>Permohonan <span class="approved"></span>Diluluskan</div>
</section>

<section class="panel report-panel welfare-category-panel">
    <div class="panel-head"><div><p class="panel-kicker">Profil Penerima</p><h3>Permohonan Mengikut Kategori OKU</h3><p>Taburan permohonan berdasarkan kategori pemohon.</p></div></div>
    <div class="report-category-list">@foreach($report['categories'] as $category=>$data)<div class="report-category-row"><div><strong>{{ $category }}</strong><span>{{ $data['total'] }} permohonan</span></div><div class="report-bar"><span style="width:{{ $data['percentage'] }}%"></span></div><b>{{ $data['percentage'] }}%</b></div>@endforeach</div>
</section>
</div>

<p class="report-generated">Dijana pada {{ $report['generated_at']->format('d/m/Y, H:i') }} · Tiada maklumat peribadi dipaparkan dalam laporan ini.</p>
@endsection
