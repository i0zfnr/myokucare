@extends('layout',['title'=>'Permohonan Kebajikan'])
@section('content')
@php
    $hasFilters=collect($filters)->except(['sort_by','sort_direction','per_page'])->filter(fn($value)=>filled($value))->isNotEmpty();
    $statusLabels=['Pending'=>'Menunggu','Under Review'=>'Dalam Semakan','Approved'=>'Diluluskan','Rejected'=>'Ditolak'];
@endphp
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.sokongan_kebajikan.db5367af') }}</p><h2>{{ __('ui.permohonan_kebajikan.33bca6ce') }}</h2><p>{{ $isStaff?'Pantau, semak dan urus tindakan bagi semua permohonan.':'Pantau permohonan bagi profil OKU yang dipautkan.' }}</p></div>
    <div class="page-actions">
        @if($isStaff)<a class="btn" href="{{ route('reports.export','welfare') }}">{{ __('ui.eksport_csv.24844a7f') }}</a>@endif
        <a class="btn btn-primary" href="{{ route('welfare.create') }}">{{ __('ui.permohonan_baharu.b9deab2e') }}</a>
    </div>
</div>

<section class="welfare-stat-grid" aria-label="{{ __('ui.ringkasan_permohonan_kebajikan.7d00b5fb') }}">
@foreach([
    ['Semua permohonan',$stats['total'],'total'],
    ['Menunggu tindakan',$stats['pending'],'pending'],
    ['Dalam semakan',$stats['review'],'review'],
    ['Diluluskan',$stats['approved'],'approved'],
] as [$label,$number,$tone])
    <article class="panel welfare-stat {{ $tone }}"><span>{{ $label }}</span><strong>{{ number_format($number) }}</strong></article>
@endforeach
</section>

<form class="panel welfare-filter-panel" method="get" action="{{ route('welfare.index') }}" role="search">
    <div class="form-group welfare-search"><label for="welfare-search">{{ __('ui.cari_permohonan.ef467d99') }}</label><input class="field" id="welfare-search" name="search" type="search" maxlength="100" value="{{ $filters['search']??'' }}" placeholder="{{ $isStaff?'Nama, nombor kad OKU atau jenis bantuan':'Jenis bantuan atau catatan' }}"></div>
    <div class="form-group"><label for="welfare-status">{{ __('ui.status.bae7d5be') }}</label><select class="select" id="welfare-status" name="status"><option value="">{{ __('ui.semua_status.baa2adda') }}</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="form-group"><label for="welfare-type">{{ __('ui.jenis_permohonan.555e1b59') }}</label><select class="select" id="welfare-type" name="type"><option value="">{{ __('ui.semua_jenis.dbf8ef8a') }}</option>@foreach($types as $type)<option value="{{ $type }}" @selected(($filters['type']??'')===$type)>{{ $type }}</option>@endforeach</select></div>
    <div class="form-group"><label for="date-from">{{ __('ui.tarikh_mula.c0b2ad4e') }}</label><input class="field" id="date-from" name="date_from" type="date" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="date-to">{{ __('ui.tarikh_akhir.b02c5275') }}</label><input class="field" id="date-to" name="date_to" type="date" value="{{ $filters['date_to']??'' }}"></div>
    <div class="welfare-filter-actions"><button class="btn btn-primary" type="submit">{{ __('ui.tapis_permohonan.b66632f4') }}</button>@if($hasFilters)<a class="btn" href="{{ route('welfare.index') }}">{{ __('ui.kosongkan_penapis.3bca173e') }}</a>@endif</div>
</form>

<div class="result-summary" role="status" aria-live="polite"><span><strong>{{ $applications->total() }}</strong> {{ __('ui.permohonan_ditemui.337dd003') }}</span></div>

<section class="panel welfare-table-panel">
<div class="table-wrap">
<table class="data-table welfare-table" aria-label="{{ __('ui.senarai_permohonan_kebajikan.63a1854d') }}">
<thead><tr><th scope="col">{{ __('ui.pemohon.115368c3') }}</th><th scope="col">{{ __('ui.jenis.fabb2b5c') }}</th><th scope="col">{{ __('ui.tarikh.bb81283e') }}</th><th scope="col">{{ __('ui.status.bae7d5be') }}</th><th scope="col">{{ __('ui.semakan_seterusnya.410aee65') }}</th><th scope="col"><span class="sr-only">{{ __('ui.tindakan.4c20e744') }}</span></th></tr></thead>
<tbody>
@forelse($applications as $application)
<tr>
    <td data-label="Pemohon">@if($isStaff)<a class="record-name" href="{{ route('oku.show',$application->oku) }}">{{ $application->oku->name }}</a>@else<strong class="record-name">{{ $application->oku->name }}</strong>@endif<small>{{ $application->oku->oku_card_number }}</small></td>
    <td data-label="Jenis"><strong>{{ $application->application_type }}</strong>@if($application->notes)<small>{{ \Illuminate\Support\Str::limit($application->notes,70) }}</small>@endif</td>
    <td data-label="Tarikh">{{ $application->application_date->format('d/m/Y') }}</td>
    <td data-label="Status"><span class="welfare-status status-{{ str($application->status)->slug() }}"><span aria-hidden="true"></span>{{ $statusLabels[$application->status] }}</span>@if($application->reviewer)<small>Oleh {{ $application->reviewer->name }}</small>@endif</td>
    <td data-label="Semakan">{{ $application->next_review_date?->format('d/m/Y')??'Belum ditetapkan' }}</td>
    <td data-label="Tindakan">
        <a class="btn case-view-button" href="{{ route('welfare.show',$application) }}">{{ __('ui.lihat.f78db130') }}</a>
        @if($isStaff)
        <details class="case-actions"><summary>{{ __('ui.urus.0ada72b6') }}</summary><div>
            <form method="post" action="{{ route('welfare.update-status',$application) }}">@csrf @method('PUT')
                <label for="status-{{ $application->id }}">Status</label>
                <select class="select" id="status-{{ $application->id }}" name="status">@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected($application->status===$value)>{{ $label }}</option>@endforeach</select>
                <input class="field" name="rejection_reason" placeholder="{{ __('ui.sebab_penolakan_jika_berkaitan.ba504c74') }}">
                <button class="btn btn-primary" type="submit">{{ __('ui.simpan_status.e162190b') }}</button>
            </form>
            <form method="post" action="{{ route('welfare.schedule-review',$application) }}">@csrf
                <label for="schedule-{{ $application->id }}">Jadual semakan</label>
                <input class="field" id="schedule-{{ $application->id }}" name="scheduled_date" type="date" min="{{ today()->format('Y-m-d') }}" required>
                <button class="btn" type="submit">{{ __('ui.tetapkan_tarikh.321267f9') }}</button>
            </form>
        </div></details>
        @else
        <span class="case-readonly">{{ __('ui.paparan_sahaja.665cccb3') }}</span>
        @endif
    </td>
</tr>
@empty
<tr><td class="empty welfare-empty" colspan="6"><span aria-hidden="true">♡</span><strong>{{ __('ui.tiada_permohonan_ditemui.7744e08f') }}</strong><p>{{ __('ui.belum_ada_rekod_atau_cuba_ubah_penapis.25554808') }}</p></td></tr>
@endforelse
</tbody></table>
</div></section>
<div class="pagination">{{ $applications->links() }}</div>
@endsection
