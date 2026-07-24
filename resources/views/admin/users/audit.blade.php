@extends('layout',['title'=>'Audit Aktiviti'])
@section('content')
<div class="page-head"><div><p class="eyebrow">Keselamatan Sistem</p><h2>Audit Aktiviti Pengguna</h2><p>Jejak perubahan akaun yang dilakukan oleh Admin System.</p></div><div class="page-actions"><a class="btn" href="{{ route('admin.audit.export',$filters) }}">Eksport CSV</a><a class="btn btn-primary" href="{{ route('admin.users.index') }}">Pengurusan Pengguna</a></div></div>

<section class="audit-stat-grid" aria-label="Statistik log audit">
@foreach([['Jumlah aktiviti',$statistics['total'],'total'],['Hari ini',$statistics['today'],'today'],['Minggu ini',$statistics['week'],'week'],['Perlu perhatian',$statistics['warning'],'warning']] as [$label,$value,$tone])
<article class="panel audit-stat {{ $tone }}"><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>
@endforeach
</section>

<form class="panel audit-filter-panel" method="get" action="{{ route('admin.audit') }}" role="search" aria-label="Cari dan tapis log audit">
    <div class="form-group audit-search"><label for="audit-search">Cari pengguna</label><input class="field" id="audit-search" name="search" value="{{ $filters['search']??'' }}" maxlength="100" placeholder="Nama pentadbir, pengguna atau e-mel"></div>
    <div class="form-group"><label for="audit-action">Aktiviti</label><select class="select" id="audit-action" name="action"><option value="">Semua aktiviti</option>@foreach($actions as $value=>$data)<option value="{{ $value }}" @selected(($filters['action']??'')===$value)>{{ $data['label'] }}</option>@endforeach</select></div>
    <div class="form-group"><label for="audit-severity">Tahap</label><select class="select" id="audit-severity" name="severity"><option value="">Semua tahap</option><option value="info" @selected(($filters['severity']??'')==='info')>Maklumat</option><option value="warning" @selected(($filters['severity']??'')==='warning')>Perlu perhatian</option></select></div>
    <div class="form-group"><label for="audit-from">Tarikh mula</label><input class="field" id="audit-from" name="date_from" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="audit-to">Tarikh akhir</label><input class="field" id="audit-to" name="date_to" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ $filters['date_to']??'' }}"></div>
    <div class="form-group"><label for="audit-page-size">Paparan</label><select class="select" id="audit-page-size" name="per_page">@foreach([10,20,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??20)===$size)>{{ $size }} rekod</option>@endforeach</select></div>
    <input type="hidden" name="sort_direction" value="{{ $filters['sort_direction']??'desc' }}">
    <div class="audit-filter-actions"><button class="btn btn-primary" type="submit">Tapis Log</button>@if(request()->query())<a class="btn" href="{{ route('admin.audit') }}">Kosongkan</a>@endif</div>
</form>

<div class="result-summary" role="status" aria-live="polite"><span><strong>{{ $logs->total() }}</strong> aktiviti ditemui</span><a href="{{ route('admin.audit',array_merge(request()->query(),['sort_direction'=>($filters['sort_direction']??'desc')==='desc'?'asc':'desc'])) }}">Susunan: {{ ($filters['sort_direction']??'desc')==='desc'?'Terbaharu':'Terlama' }}</a></div>

<section class="audit-list" aria-label="Senarai log audit" aria-live="polite">
@forelse($logs as $log)
@php $action=$auditService->action($log->action); @endphp
<article class="panel audit-entry severity-{{ $action['severity'] }}" aria-label="{{ $action['label'] }} pada {{ $log->created_at->format('d/m/Y H:i') }}">
    <span class="audit-icon" aria-hidden="true"><x-dashboard-icon name="audit"/></span>
    <div>
        <div class="audit-entry-head"><div><strong>{{ $action['label'] }}</strong><span class="audit-severity">{{ $action['severity']==='warning'?'Perlu perhatian':'Maklumat' }}</span></div><time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d/m/Y H:i:s') }}</time></div>
        <p><b>{{ $log->actor?->name??'Akaun dipadam' }}</b> melakukan perubahan pada <b>{{ $log->subject?->name??'sistem audit' }}</b>@if($log->subject) <span>({{ $log->subject->email }})</span>@endif.</p>
        <small class="audit-description">{{ $action['description'] }}</small>
        @if($log->changes)<dl class="audit-changes">@foreach($log->changes as $field=>$value)<div><dt>{{ str($field)->replace('_',' ')->title() }}</dt><dd>{{ is_array($value)?json_encode($value,JSON_UNESCAPED_UNICODE):(is_bool($value)?($value?'Ya':'Tidak'):($value??'Kosong')) }}</dd></div>@endforeach</dl>@endif
        <div class="audit-metadata"><span>IP: {{ $auditService->maskIp($log->ip_address) }}</span>@if($log->user_agent)<span title="{{ $log->user_agent }}">{{ str($log->user_agent)->limit(55) }}</span>@endif</div>
    </div>
</article>
@empty
<section class="panel audit-empty" role="status"><span aria-hidden="true">⌕</span><h3>Tiada log audit ditemui</h3><p>Cuba ubah penapis atau kosongkan carian semasa.</p>@if(request()->query())<a class="btn" href="{{ route('admin.audit') }}">Kosongkan Penapis</a>@endif</section>
@endforelse
</section>
<div class="pagination">{{ $logs->links('components.pagination') }}</div>
@endsection
