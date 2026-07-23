@extends('layout',['title'=>'Permohonan Kebajikan'])
@section('content')
@php
    $hasFilters=collect($filters)->except(['sort_by','sort_direction','per_page'])->filter(fn($value)=>filled($value))->isNotEmpty();
    $statusLabels=['Pending'=>'Menunggu','Under Review'=>'Dalam Semakan','Approved'=>'Diluluskan','Rejected'=>'Ditolak'];
@endphp
<div class="page-head">
    <div><p class="eyebrow">Sokongan Kebajikan</p><h2>Permohonan Kebajikan</h2><p>{{ $isStaff?'Pantau, semak dan urus tindakan bagi semua permohonan.':'Pantau permohonan bagi profil OKU yang dipautkan.' }}</p></div>
    <div class="page-actions">
        @if($isStaff)<a class="btn" href="{{ route('reports.export','welfare') }}">Eksport CSV</a>@endif
        <a class="btn btn-primary" href="{{ route('welfare.create') }}">Permohonan Baharu</a>
    </div>
</div>

<section class="welfare-stat-grid" aria-label="Ringkasan permohonan kebajikan">
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
    <div class="form-group welfare-search"><label for="welfare-search">Cari permohonan</label><input class="field" id="welfare-search" name="search" type="search" maxlength="100" value="{{ $filters['search']??'' }}" placeholder="{{ $isStaff?'Nama, nombor kad OKU atau jenis bantuan':'Jenis bantuan atau catatan' }}"></div>
    <div class="form-group"><label for="welfare-status">Status</label><select class="select" id="welfare-status" name="status"><option value="">Semua status</option>@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select></div>
    <div class="form-group"><label for="welfare-type">Jenis permohonan</label><select class="select" id="welfare-type" name="type"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type }}" @selected(($filters['type']??'')===$type)>{{ $type }}</option>@endforeach</select></div>
    <div class="form-group"><label for="date-from">Tarikh mula</label><input class="field" id="date-from" name="date_from" type="date" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="date-to">Tarikh akhir</label><input class="field" id="date-to" name="date_to" type="date" value="{{ $filters['date_to']??'' }}"></div>
    <div class="welfare-filter-actions"><button class="btn btn-primary" type="submit">Tapis Permohonan</button>@if($hasFilters)<a class="btn" href="{{ route('welfare.index') }}">Kosongkan Penapis</a>@endif</div>
</form>

<div class="result-summary" role="status" aria-live="polite"><span><strong>{{ $applications->total() }}</strong> permohonan ditemui</span></div>

<section class="panel welfare-table-panel">
<div class="table-wrap">
<table class="data-table welfare-table" aria-label="Senarai permohonan kebajikan">
<thead><tr><th scope="col">Pemohon</th><th scope="col">Jenis</th><th scope="col">Tarikh</th><th scope="col">Status</th><th scope="col">Semakan seterusnya</th><th scope="col"><span class="sr-only">Tindakan</span></th></tr></thead>
<tbody>
@forelse($applications as $application)
<tr>
    <td data-label="Pemohon">@if($isStaff)<a class="record-name" href="{{ route('oku.show',$application->oku) }}">{{ $application->oku->name }}</a>@else<strong class="record-name">{{ $application->oku->name }}</strong>@endif<small>{{ $application->oku->oku_card_number }}</small></td>
    <td data-label="Jenis"><strong>{{ $application->application_type }}</strong>@if($application->notes)<small>{{ \Illuminate\Support\Str::limit($application->notes,70) }}</small>@endif</td>
    <td data-label="Tarikh">{{ $application->application_date->format('d/m/Y') }}</td>
    <td data-label="Status"><span class="welfare-status status-{{ str($application->status)->slug() }}"><span aria-hidden="true"></span>{{ $statusLabels[$application->status] }}</span>@if($application->reviewer)<small>Oleh {{ $application->reviewer->name }}</small>@endif</td>
    <td data-label="Semakan">{{ $application->next_review_date?->format('d/m/Y')??'Belum ditetapkan' }}</td>
    <td data-label="Tindakan">
        <a class="btn case-view-button" href="{{ route('welfare.show',$application) }}">Lihat</a>
        @if($isStaff)
        <details class="case-actions"><summary>Urus</summary><div>
            <form method="post" action="{{ route('welfare.update-status',$application) }}">@csrf @method('PUT')
                <label for="status-{{ $application->id }}">Status</label>
                <select class="select" id="status-{{ $application->id }}" name="status">@foreach($statusLabels as $value=>$label)<option value="{{ $value }}" @selected($application->status===$value)>{{ $label }}</option>@endforeach</select>
                <input class="field" name="rejection_reason" placeholder="Sebab penolakan, jika berkaitan">
                <button class="btn btn-primary" type="submit">Simpan Status</button>
            </form>
            <form method="post" action="{{ route('welfare.schedule-review',$application) }}">@csrf
                <label for="schedule-{{ $application->id }}">Jadual semakan</label>
                <input class="field" id="schedule-{{ $application->id }}" name="scheduled_date" type="date" min="{{ today()->format('Y-m-d') }}" required>
                <button class="btn" type="submit">Tetapkan Tarikh</button>
            </form>
        </div></details>
        @else
        <span class="case-readonly">Paparan sahaja</span>
        @endif
    </td>
</tr>
@empty
<tr><td class="empty welfare-empty" colspan="6"><span aria-hidden="true">♡</span><strong>Tiada permohonan ditemui</strong><p>Belum ada rekod atau cuba ubah penapis semasa.</p></td></tr>
@endforelse
</tbody></table>
</div></section>
<div class="pagination">{{ $applications->links() }}</div>
@endsection
