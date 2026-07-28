@extends('layout',['title'=>'Laporan Kebajikan'])
@section('content')
@php
    $summary=$report['summary'];
    $maxType=max(1,$report['types']->max('total')??0);
    $maxMonth=max(1,$report['monthly']->max('total'));
    $statusLabels=['Pending'=>'Menunggu','Under Review'=>'Dalam Semakan','Approved'=>'Diluluskan','Rejected'=>'Ditolak'];
@endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.analitik_kebajikan.7c39f612') }}</p><h2>{{ __('ui.statistik_kebajikan_oku.5d1de92c') }}</h2><p>{{ __('ui.analisis_agregat_beban_kes_keputusan_dan_keperluan.704dea11') }}</p></div>
    <div class="page-actions report-actions"><button class="btn" type="button" data-print-report>{{ __('ui.cetak_simpan_pdf.48b209ee') }}</button><a class="btn btn-primary" href="{{ route('reports.welfare-export',$filters) }}">{{ __('ui.eksport_statistik_csv.f936c485') }}</a></div>
</div>

<form class="panel report-filter-panel welfare-report-filter" method="get" action="{{ route('reports.welfare') }}" aria-label="{{ __('ui.tapis_laporan_kebajikan.1eecd736') }}">
    <div class="form-group"><label for="welfare-report-from">{{ __('ui.tarikh_mula.c0b2ad4e') }}</label><input class="field" id="welfare-report-from" name="date_from" type="date" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="welfare-report-to">{{ __('ui.tarikh_akhir.b02c5275') }}</label><input class="field" id="welfare-report-to" name="date_to" type="date" value="{{ $filters['date_to']??'' }}"></div>
    <div class="form-group"><label for="welfare-report-status">{{ __('ui.status.bae7d5be') }}</label><select class="select" id="welfare-report-status" name="status"><option value="">{{ __('ui.semua_status.baa2adda') }}</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="form-group"><label for="welfare-report-type">{{ __('ui.jenis_bantuan.07e50bb0') }}</label><select class="select" id="welfare-report-type" name="type"><option value="">{{ __('ui.semua_jenis.dbf8ef8a') }}</option>@foreach($types as $type)<option value="{{ $type }}" @selected(($filters['type']??'')===$type)>{{ $type }}</option>@endforeach</select></div>
    <div class="form-group"><label for="welfare-report-category">{{ __('ui.kategori_oku.5a4ba70d') }}</label><select class="select" id="welfare-report-category" name="category"><option value="">{{ __('ui.semua_kategori.3ee43aaf') }}</option>@foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $category)<option value="{{ $category }}" @selected(($filters['category']??'')===$category)>{{ $category }}</option>@endforeach</select></div>
    <div class="report-filter-actions"><button class="btn btn-primary" type="submit">{{ __('ui.jana_laporan.f466bd2e') }}</button>@if(request()->query())<a class="btn" href="{{ route('reports.welfare') }}">{{ __('ui.tetapkan_semula.d63d5ccf') }}</a>@endif</div>
</form>

<div class="report-scope" aria-label="{{ __('ui.skop_laporan.402d66b4') }}">
    <strong>{{ __('ui.skop_laporan.285eed85') }}</strong>
    <span>{{ $filters['date_from']??'Semua tarikh' }} hingga {{ $filters['date_to']??'semasa' }}</span>
    @if(filled($filters['status']??null))<span>Status: {{ $statusLabels[$filters['status']] }}</span>@endif
    @if(filled($filters['type']??null))<span>Jenis: {{ $filters['type'] }}</span>@endif
    @if(filled($filters['category']??null))<span>Kategori: {{ $filters['category'] }}</span>@endif
</div>

<section class="report-metric-grid welfare-report-metrics" aria-label="{{ __('ui.ringkasan_statistik_kebajikan.519e6305') }}" role="status">
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
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.keperluan_bantuan.ea18d7a8') }}</p><h3>{{ __('ui.permohonan_mengikut_jenis.5634f43f') }}</h3><p>{{ __('ui.jenis_bantuan_yang_paling_kerap_dimohon.a6367c88') }}</p></div></div>
    @if($report['types']->isNotEmpty())
    <div class="welfare-type-list">
    @foreach($report['types'] as $type=>$data)
        <div><div><strong>{{ $type }}</strong><span>{{ $data['approved'] }} diluluskan</span></div><div class="report-bar"><span style="width:{{ ($data['total']/$maxType)*100 }}%"></span></div><b>{{ $data['total'] }}</b></div>
    @endforeach
    </div>
    @else
    <div class="report-empty">{{ __('ui.belum_ada_permohonan_dalam_skop_laporan_ini.7673720c') }}</div>
    @endif
</section>

<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.keputusan_kes.aa9f4d53') }}</p><h3>{{ __('ui.taburan_status.c62c12f4') }}</h3><p>{{ __('ui.komposisi_status_permohonan_semasa.a7735300') }}</p></div></div>
    @php
        $pendingPercent=$summary['total']?round(($summary['pending']/$summary['total'])*100,1):0;
        $reviewPercent=$summary['total']?round(($summary['under_review']/$summary['total'])*100,1):0;
        $approvedPercent=$summary['total']?round(($summary['approved']/$summary['total'])*100,1):0;
    @endphp
    <div class="report-donut-wrap">
        <div class="report-donut welfare-donut" style="--pending:{{ $pendingPercent }}%;--review:{{ $pendingPercent+$reviewPercent }}%;--approved:{{ $pendingPercent+$reviewPercent+$approvedPercent }}%" role="img" aria-label="{{ __('ui.taburan_status_permohonan.8f1d9180') }}"><span><strong>{{ $summary['total'] }}</strong>{{ __('ui.permohonan.2adeb904') }}</span></div>
        <ul class="welfare-status-legend">@foreach($report['statuses'] as $status=>$total)<li class="{{ str($status)->slug() }}"><span></span>{{ $statusLabels[$status] }} <b>{{ $total }}</b></li>@endforeach</ul>
    </div>
</section>

<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.pemantauan_operasi.c6fdb292') }}</p><h3>{{ __('ui.kecekapan_pemprosesan.09949f21') }}</h3><p>{{ __('ui.petunjuk_untuk_tindakan_dan_jadual_semakan_jkm.230244bc') }}</p></div></div>
    <div class="processing-metrics"><div><span>{{ __('ui.purata_masa_keputusan.16351ce6') }}</span><strong>{{ $summary['average_processing_days'] }}</strong><small>{{ __('ui.hari.46ebaaa2') }}</small></div><div class="{{ $summary['overdue_reviews']?'needs-attention':'' }}"><span>{{ __('ui.semakan_telah_lewat.daa3a140') }}</span><strong>{{ $summary['overdue_reviews'] }}</strong><small>{{ __('ui.kes.46cbe2ad') }}</small></div><div><span>{{ __('ui.kes_telah_diputuskan.3c1eb176') }}</span><strong>{{ $summary['approved']+$summary['rejected'] }}</strong><small>{{ __('ui.kes.46cbe2ad') }}</small></div></div>
</section>

<section class="panel report-panel">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.enam_bulan.8cd83b7d') }}</p><h3>{{ __('ui.trend_permohonan.a0a3f327') }}</h3><p>{{ __('ui.jumlah_permohonan_dan_kelulusan_bulanan.cd78813e') }}</p></div></div>
    <div class="welfare-trend" role="img" aria-label="{{ __('ui.trend_permohonan_kebajikan_enam_bulan.9ba24a98') }}">@foreach($report['monthly'] as $month)<div><div><span class="total" style="height:{{ max(5,($month['total']/$maxMonth)*100) }}%"><b>{{ $month['total'] }}</b></span><span class="approved" style="height:{{ max(3,($month['approved']/$maxMonth)*100) }}%"><b>{{ $month['approved'] }}</b></span></div><small>{{ $month['label'] }}</small></div>@endforeach</div>
    <div class="trend-legend"><span class="total"></span>{{ __('ui.permohonan.2adeb904') }} <span class="approved"></span>{{ __('ui.diluluskan.ab8165fe') }}</div>
</section>

<section class="panel report-panel welfare-category-panel">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.profil_penerima.32924eac') }}</p><h3>{{ __('ui.permohonan_mengikut_kategori_oku.f70f04e9') }}</h3><p>{{ __('ui.taburan_permohonan_berdasarkan_kategori_pemohon.dd781510') }}</p></div></div>
    <div class="report-category-list">@foreach($report['categories'] as $category=>$data)<div class="report-category-row"><div><strong>{{ $category }}</strong><span>{{ $data['total'] }} permohonan</span></div><div class="report-bar"><span style="width:{{ $data['percentage'] }}%"></span></div><b>{{ $data['percentage'] }}%</b></div>@endforeach</div>
</section>
</div>

<p class="report-generated">Dijana pada {{ $report['generated_at']->format('d/m/Y, H:i') }} · Tiada maklumat peribadi dipaparkan dalam laporan ini.</p>
@endsection
