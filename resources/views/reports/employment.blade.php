@extends('layout',['title'=>'Laporan Pekerjaan'])
@section('content')
@php
    $summary=$report['summary'];
    $maxAge=max(1,$report['age_groups']->max());
    $maxMonth=max(1,$report['monthly']->max('total'));
    $exportQuery=array_merge($filters);
@endphp
<div class="page-head">
    <div><p class="eyebrow">Analitik Pekerjaan</p><h2>Statistik Pekerjaan OKU</h2><p>Analisis agregat untuk membantu perancangan sokongan dan penempatan pekerjaan.</p></div>
    <div class="page-actions report-actions"><button class="btn" type="button" data-print-report>Cetak / Simpan PDF</button><a class="btn btn-primary" href="{{ route('reports.employment-export',$exportQuery) }}">Eksport Statistik CSV</a></div>
</div>

<form class="panel report-filter-panel" method="get" action="{{ route('reports.employment') }}" aria-label="Tapis laporan pekerjaan">
    <div class="form-group"><label for="report-date-from">Tarikh daftar mula</label><input class="field" id="report-date-from" name="date_from" type="date" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="report-date-to">Tarikh daftar akhir</label><input class="field" id="report-date-to" name="date_to" type="date" value="{{ $filters['date_to']??'' }}"></div>
    <div class="form-group"><label for="report-category">Kategori OKU</label><select class="select" id="report-category" name="category"><option value="">Semua kategori</option>@foreach(['Fizikal','Pendengaran','Mental','Pembelajaran','Penglihatan'] as $category)<option value="{{ $category }}" @selected(($filters['category']??'')===$category)>{{ $category }}</option>@endforeach</select></div>
    <div class="form-group"><label for="report-gender">Jantina</label><select class="select" id="report-gender" name="gender"><option value="">Semua jantina</option><option value="Lelaki" @selected(($filters['gender']??'')==='Lelaki')>Lelaki</option><option value="Perempuan" @selected(($filters['gender']??'')==='Perempuan')>Perempuan</option></select></div>
    <div class="form-group"><label for="report-age-min">Umur minimum</label><input class="field" id="report-age-min" name="age_min" type="number" min="1" max="120" value="{{ $filters['age_min']??'' }}"></div>
    <div class="form-group"><label for="report-age-max">Umur maksimum</label><input class="field" id="report-age-max" name="age_max" type="number" min="1" max="120" value="{{ $filters['age_max']??'' }}"></div>
    <div class="report-filter-actions"><button class="btn btn-primary" type="submit">Jana Laporan</button>@if(request()->query())<a class="btn" href="{{ route('reports.employment') }}">Tetapkan Semula</a>@endif</div>
</form>

<div class="report-scope" aria-label="Skop laporan">
    <strong>Skop laporan:</strong>
    <span>{{ $filters['date_from']??'Semua tarikh' }} hingga {{ $filters['date_to']??'semasa' }}</span>
    @if(filled($filters['category']??null))<span>Kategori: {{ $filters['category'] }}</span>@endif
    @if(filled($filters['gender']??null))<span>Jantina: {{ $filters['gender'] }}</span>@endif
    @if(isset($filters['age_min'])||isset($filters['age_max']))<span>Umur: {{ $filters['age_min']??1 }}–{{ $filters['age_max']??120 }} tahun</span>@endif
</div>

<section class="report-metric-grid" aria-label="Ringkasan statistik pekerjaan" role="status">
@foreach([
    ['Jumlah OKU',$summary['total'],'Rekod dalam skop laporan','total'],
    ['Sedang Bekerja',$summary['employed'],'Pekerjaan bergaji','employed'],
    ['Bekerja Sendiri',$summary['self_employed'],'Pekerjaan kendiri','self'],
    ['Belum Bekerja',$summary['unemployed'],'Memerlukan sokongan','unemployed'],
    ['Kadar Bekerja',$summary['employment_rate'].'%','Termasuk bekerja sendiri','rate'],
] as [$label,$value,$caption,$tone])
<article class="panel report-metric {{ $tone }}" aria-label="{{ $label }}: {{ $value }}"><span>{{ $label }}</span><strong>{{ is_numeric($value)?number_format($value):$value }}</strong><small>{{ $caption }}</small></article>
@endforeach
</section>

<div class="report-grid">
<section class="panel report-panel category-report">
    <div class="panel-head"><div><p class="panel-kicker">Perbandingan</p><h3>Pekerjaan Mengikut Kategori OKU</h3><p>Bilangan bekerja termasuk pekerjaan sendiri.</p></div></div>
    <div class="report-category-list">
    @foreach($report['categories'] as $category=>$data)
        <div class="report-category-row">
            <div><strong>{{ $category }}</strong><span>{{ $data['working'] }} bekerja · {{ $data['unemployed'] }} belum bekerja</span></div>
            <div class="report-bar" aria-label="{{ $data['rate'] }} peratus bekerja"><span style="width:{{ $data['rate'] }}%"></span></div>
            <b>{{ $data['rate'] }}%</b>
        </div>
    @endforeach
    </div>
</section>

<section class="panel report-panel status-report">
    <div class="panel-head"><div><p class="panel-kicker">Status Semasa</p><h3>Taburan Status Pekerjaan</h3><p>Komposisi rekod dalam skop pilihan.</p></div></div>
    @php
        $workingPercent=$summary['total']?round(($summary['employed']/$summary['total'])*100,1):0;
        $selfPercent=$summary['total']?round(($summary['self_employed']/$summary['total'])*100,1):0;
    @endphp
    <div class="report-donut-wrap">
        <div class="report-donut" style="--working:{{ $workingPercent }}%;--self:{{ $workingPercent+$selfPercent }}%" role="img" aria-label="{{ $workingPercent }} peratus bekerja, {{ $selfPercent }} peratus bekerja sendiri"><span><strong>{{ $summary['employment_rate'] }}%</strong>Kadar bekerja</span></div>
        <ul><li class="working"><span></span>Bekerja <b>{{ $summary['employed'] }}</b></li><li class="self"><span></span>Sendiri <b>{{ $summary['self_employed'] }}</b></li><li class="not-working"><span></span>Belum bekerja <b>{{ $summary['unemployed'] }}</b></li></ul>
    </div>
</section>

<section class="panel report-panel age-report">
    <div class="panel-head"><div><p class="panel-kicker">Demografi</p><h3>Taburan Umur</h3><p>Bilangan OKU berdaftar mengikut kumpulan umur.</p></div></div>
    <div class="age-report-list">@foreach($report['age_groups'] as $label=>$total)<div><span>{{ $label }}</span><div><i style="width:{{ ($total/$maxAge)*100 }}%"></i></div><strong>{{ $total }}</strong></div>@endforeach</div>
</section>

<section class="panel report-panel trend-report">
    <div class="panel-head"><div><p class="panel-kicker">Enam Bulan</p><h3>Trend Pendaftaran OKU</h3><p>Rekod baharu mengikut bulan.</p></div></div>
    <div class="trend-bars" role="img" aria-label="Trend pendaftaran enam bulan">@foreach($report['monthly'] as $month)<div><span style="height:{{ max(5,($month['total']/$maxMonth)*100) }}%"><b>{{ $month['total'] }}</b></span><small>{{ $month['label'] }}</small></div>@endforeach</div>
</section>
</div>

<p class="report-generated">Dijana pada {{ $report['generated_at']->format('d/m/Y, H:i') }} · Data agregat semasa daripada MyOKUcare.</p>
@endsection
