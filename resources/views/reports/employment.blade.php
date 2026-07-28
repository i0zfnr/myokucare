@extends('layout', ['title' => __('reports.employment.title')])
@section('content')
@php
    $summary = $report['summary'];
    $maxAge = max(1, $report['age_groups']->max());
    $maxMonth = max(1, $report['monthly']->max('total'));
    $exportQuery = array_merge($filters);
@endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.analitik_pekerjaan.73f24a3b') }}</p><h2>{{ __('ui.statistik_pekerjaan_oku.ab1e7f32') }}</h2><p>{{ __('ui.analisis_agregat_untuk_membantu_perancangan_sokongan_dan.f69801ef') }}</p></div>
    <div class="page-actions report-actions"><button class="btn" type="button" data-print-report>{{ __('ui.cetak_simpan_pdf.48b209ee') }}</button><a class="btn btn-primary" href="{{ route('reports.employment-export', $exportQuery) }}">{{ __('ui.eksport_statistik_csv.f936c485') }}</a></div>
</div>

<form class="panel report-filter-panel" method="get" action="{{ route('reports.employment') }}" aria-label="{{ __('ui.tapis_laporan_pekerjaan.ac985515') }}">
    <div class="form-group"><label for="report-date-from">{{ __('ui.tarikh_daftar_mula.247398ec') }}</label><input class="field" id="report-date-from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></div>
    <div class="form-group"><label for="report-date-to">{{ __('ui.tarikh_daftar_akhir.f6dccc3f') }}</label><input class="field" id="report-date-to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></div>
    <div class="form-group"><label for="report-category">{{ __('ui.kategori_oku.5a4ba70d') }}</label><select class="select" id="report-category" name="category"><option value="">{{ __('ui.semua_kategori.3ee43aaf') }}</option>@foreach(['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan'] as $category)<option value="{{ $category }}" @selected(($filters['category'] ?? '') === $category)>{{ __("options.oku_category.$category") }}</option>@endforeach</select></div>
    <div class="form-group"><label for="report-gender">{{ __('ui.jantina.652f8b82') }}</label><select class="select" id="report-gender" name="gender"><option value="">{{ __('ui.semua_jantina.92fafb66') }}</option><option value="Lelaki" @selected(($filters['gender'] ?? '') === 'Lelaki')>{{ __('ui.lelaki.0555ffac') }}</option><option value="Perempuan" @selected(($filters['gender'] ?? '') === 'Perempuan')>{{ __('ui.perempuan.bc79719d') }}</option></select></div>
    <div class="form-group"><label for="report-age-min">{{ __('ui.umur_minimum.b750d323') }}</label><input class="field" id="report-age-min" name="age_min" type="number" min="1" max="120" value="{{ $filters['age_min'] ?? '' }}"></div>
    <div class="form-group"><label for="report-age-max">{{ __('ui.umur_maksimum.37af54d6') }}</label><input class="field" id="report-age-max" name="age_max" type="number" min="1" max="120" value="{{ $filters['age_max'] ?? '' }}"></div>
    <div class="report-filter-actions"><button class="btn btn-primary" type="submit">{{ __('ui.jana_laporan.f466bd2e') }}</button>@if(request()->query())<a class="btn" href="{{ route('reports.employment') }}">{{ __('ui.tetapkan_semula.d63d5ccf') }}</a>@endif</div>
</form>

<div class="report-scope" aria-label="{{ __('ui.skop_laporan.402d66b4') }}">
    <strong>{{ __('ui.skop_laporan.285eed85') }}</strong>
    <span>{{ __('reports.employment.scope_range', ['from' => $filters['date_from'] ?? __('reports.employment.all_dates'), 'to' => $filters['date_to'] ?? __('reports.employment.current')]) }}</span>
    @if(filled($filters['category'] ?? null))<span>{{ __('reports.employment.category_filter', ['category' => __("options.oku_category.{$filters['category']}")]) }}</span>@endif
    @if(filled($filters['gender'] ?? null))<span>{{ __('reports.employment.gender_filter', ['gender' => __("options.gender.{$filters['gender']}")]) }}</span>@endif
    @if(isset($filters['age_min']) || isset($filters['age_max']))<span>{{ __('reports.employment.age_filter', ['min' => $filters['age_min'] ?? 1, 'max' => $filters['age_max'] ?? 120]) }}</span>@endif
</div>

<section class="report-metric-grid" aria-label="{{ __('ui.ringkasan_statistik_pekerjaan.0159670f') }}" role="status">
@foreach([
    [__('reports.employment.metrics.total'), $summary['total'], __('reports.employment.metrics.total_caption'), 'total'],
    [__('reports.employment.metrics.employed'), $summary['employed'], __('reports.employment.metrics.employed_caption'), 'employed'],
    [__('reports.employment.metrics.self_employed'), $summary['self_employed'], __('reports.employment.metrics.self_employed_caption'), 'self'],
    [__('reports.employment.metrics.unemployed'), $summary['unemployed'], __('reports.employment.metrics.unemployed_caption'), 'unemployed'],
    [__('reports.employment.metrics.rate'), $summary['employment_rate'].'%', __('reports.employment.metrics.rate_caption'), 'rate'],
] as [$label, $value, $caption, $tone])
<article class="panel report-metric {{ $tone }}" aria-label="{{ $label }}: {{ $value }}"><span>{{ $label }}</span><strong>{{ is_numeric($value) ? number_format($value) : $value }}</strong><small>{{ $caption }}</small></article>
@endforeach
</section>

<div class="report-grid">
<section class="panel report-panel category-report">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.perbandingan.e5d69832') }}</p><h3>{{ __('ui.pekerjaan_mengikut_kategori_oku.41adf191') }}</h3><p>{{ __('ui.bilangan_bekerja_termasuk_pekerjaan_sendiri.44a296d8') }}</p></div></div>
    <div class="report-category-list">
    @foreach($report['categories'] as $category => $data)
        <div class="report-category-row">
            <div><strong>{{ __("options.oku_category.$category") }}</strong><span>{{ __('reports.employment.category_counts', ['working' => $data['working'], 'unemployed' => $data['unemployed']]) }}</span></div>
            <div class="report-bar" aria-label="{{ __('reports.employment.percent_working', ['percent' => $data['rate']]) }}"><span style="width:{{ $data['rate'] }}%"></span></div>
            <b>{{ $data['rate'] }}%</b>
        </div>
    @endforeach
    </div>
</section>

<section class="panel report-panel status-report">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.status_semasa.8d59a836') }}</p><h3>{{ __('ui.taburan_status_pekerjaan.28ab8ed9') }}</h3><p>{{ __('ui.komposisi_rekod_dalam_skop_pilihan.e60ce8ef') }}</p></div></div>
    @php
        $workingPercent = $summary['total'] ? round(($summary['employed'] / $summary['total']) * 100, 1) : 0;
        $selfPercent = $summary['total'] ? round(($summary['self_employed'] / $summary['total']) * 100, 1) : 0;
    @endphp
    <div class="report-donut-wrap">
        <div class="report-donut" style="--working:{{ $workingPercent }}%;--self:{{ $workingPercent + $selfPercent }}%" role="img" aria-label="{{ __('reports.employment.donut_label', ['working' => $workingPercent, 'self' => $selfPercent]) }}"><span><strong>{{ $summary['employment_rate'] }}%</strong>{{ __('ui.kadar_bekerja.daba640c') }}</span></div>
        <ul><li class="working"><span></span>{{ __('ui.bekerja.fa64d559') }} <b>{{ $summary['employed'] }}</b></li><li class="self"><span></span>{{ __('ui.sendiri.7209d86e') }} <b>{{ $summary['self_employed'] }}</b></li><li class="not-working"><span></span>{{ __('ui.belum_bekerja.9cbeb222') }} <b>{{ $summary['unemployed'] }}</b></li></ul>
    </div>
</section>

<section class="panel report-panel age-report">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.demografi.ae448bae') }}</p><h3>{{ __('ui.taburan_umur.b1d6d472') }}</h3><p>{{ __('ui.bilangan_oku_berdaftar_mengikut_kumpulan_umur.74915e02') }}</p></div></div>
    <div class="age-report-list">@foreach($report['age_groups'] as $label => $total)<div><span>{{ $label }}</span><div><i style="width:{{ ($total / $maxAge) * 100 }}%"></i></div><strong>{{ $total }}</strong></div>@endforeach</div>
</section>

<section class="panel report-panel trend-report">
    <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.enam_bulan.8cd83b7d') }}</p><h3>{{ __('ui.trend_pendaftaran_oku.168ed79d') }}</h3><p>{{ __('ui.rekod_baharu_mengikut_bulan.1fab0ced') }}</p></div></div>
    <div class="trend-bars" role="img" aria-label="{{ __('ui.trend_pendaftaran_enam_bulan.8034dbf9') }}">@foreach($report['monthly'] as $month)<div><span style="height:{{ max(5, ($month['total'] / $maxMonth) * 100) }}%"><b>{{ $month['total'] }}</b></span><small>{{ $month['label'] }}</small></div>@endforeach</div>
</section>
</div>

<p class="report-generated">{{ __('reports.employment.generated_note', ['time' => $report['generated_at']->format('d/m/Y, H:i')]) }}</p>
@endsection
