@extends('layout',['title'=>'Audit Aktiviti'])
@section('content')
<div class="page-head"><div><p class="eyebrow">{{ __('ui.keselamatan_sistem.c2edd527') }}</p><h2>{{ __('ui.audit_aktiviti_pengguna.bb21d1a7') }}</h2><p>{{ __('ui.jejak_perubahan_akaun_yang_dilakukan_oleh_admin.f2dcbc67') }}</p></div><div class="page-actions"><a class="btn" href="{{ route('admin.audit.export',$filters) }}">{{ __('ui.eksport_csv.24844a7f') }}</a><a class="btn btn-primary" href="{{ route('admin.users.index') }}">{{ __('ui.pengurusan_pengguna.e14f116f') }}</a></div></div>

<section class="audit-stat-grid" aria-label="{{ __('ui.statistik_log_audit.72422874') }}">
@foreach([['Jumlah aktiviti',$statistics['total'],'total'],['Hari ini',$statistics['today'],'today'],['Minggu ini',$statistics['week'],'week'],['Perlu perhatian',$statistics['warning'],'warning']] as [$label,$value,$tone])
<article class="panel audit-stat {{ $tone }}"><span>{{ $label }}</span><strong>{{ number_format($value) }}</strong></article>
@endforeach
</section>

<form class="panel audit-filter-panel" method="get" action="{{ route('admin.audit') }}" role="search" aria-label="{{ __('ui.cari_dan_tapis_log_audit.ed766c3a') }}">
    <div class="form-group audit-search"><label for="audit-search">{{ __('ui.cari_pengguna.02f2f4c5') }}</label><input class="field" id="audit-search" name="search" value="{{ $filters['search']??'' }}" maxlength="100" placeholder="{{ __('ui.nama_pentadbir_pengguna_atau_e_mel.ce73a5db') }}"></div>
    <div class="form-group"><label for="audit-action">{{ __('ui.aktiviti.82397dea') }}</label><select class="select" id="audit-action" name="action"><option value="">{{ __('ui.semua_aktiviti.bbdf84f9') }}</option>@foreach($actions as $value=>$data)<option value="{{ $value }}" @selected(($filters['action']??'')===$value)>{{ $data['label'] }}</option>@endforeach</select></div>
    <div class="form-group"><label for="audit-severity">{{ __('ui.tahap.cb88a6ac') }}</label><select class="select" id="audit-severity" name="severity"><option value="">{{ __('ui.semua_tahap.47b45513') }}</option><option value="info" @selected(($filters['severity']??'')==='info')>{{ __('ui.maklumat.11bf9483') }}</option><option value="warning" @selected(($filters['severity']??'')==='warning')>{{ __('ui.perlu_perhatian.a380122e') }}</option></select></div>
    <div class="form-group"><label for="audit-from">{{ __('ui.tarikh_mula.c0b2ad4e') }}</label><input class="field" id="audit-from" name="date_from" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ $filters['date_from']??'' }}"></div>
    <div class="form-group"><label for="audit-to">{{ __('ui.tarikh_akhir.b02c5275') }}</label><input class="field" id="audit-to" name="date_to" type="date" max="{{ today()->format('Y-m-d') }}" value="{{ $filters['date_to']??'' }}"></div>
    <div class="form-group"><label for="audit-page-size">{{ __('ui.paparan.a61c4312') }}</label><select class="select" id="audit-page-size" name="per_page">@foreach([10,20,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??20)===$size)>{{ $size }} rekod</option>@endforeach</select></div>
    <input type="hidden" name="sort_direction" value="{{ $filters['sort_direction']??'desc' }}">
    <div class="audit-filter-actions"><button class="btn btn-primary" type="submit">{{ __('ui.tapis_log.f0301c52') }}</button>@if(request()->query())<a class="btn" href="{{ route('admin.audit') }}">{{ __('ui.kosongkan.899f41b5') }}</a>@endif</div>
</form>

<div class="result-summary" role="status" aria-live="polite"><span><strong>{{ $logs->total() }}</strong> {{ __('ui.aktiviti_ditemui.cef53a05') }}</span><a href="{{ route('admin.audit',array_merge(request()->query(),['sort_direction'=>($filters['sort_direction']??'desc')==='desc'?'asc':'desc'])) }}">Susunan: {{ ($filters['sort_direction']??'desc')==='desc'?'Terbaharu':'Terlama' }}</a></div>

<section class="audit-list" aria-label="{{ __('ui.senarai_log_audit.04b7c812') }}" aria-live="polite">
@forelse($logs as $log)
@php $action=$auditService->action($log->action); @endphp
<article class="panel audit-entry severity-{{ $action['severity'] }}" aria-label="{{ $action['label'] }} pada {{ $log->created_at->format('d/m/Y H:i') }}">
    <span class="audit-icon" aria-hidden="true"><x-dashboard-icon name="audit"/></span>
    <div>
        <div class="audit-entry-head"><div><strong>{{ $action['label'] }}</strong><span class="audit-severity">{{ $action['severity']==='warning'?'Perlu perhatian':'Maklumat' }}</span></div><time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->format('d/m/Y H:i:s') }}</time></div>
        <p><b>{{ $log->actor?->name??'Akaun dipadam' }}</b> {{ __('ui.melakukan_perubahan_pada.03ac429f') }} <b>{{ $log->subject?->name??'sistem audit' }}</b>@if($log->subject) <span>({{ $log->subject->email }})</span>@endif.</p>
        <small class="audit-description">{{ $action['description'] }}</small>
        @if($log->changes)<dl class="audit-changes">@foreach($log->changes as $field=>$value)<div><dt>{{ str($field)->replace('_',' ')->title() }}</dt><dd>{{ is_array($value)?json_encode($value,JSON_UNESCAPED_UNICODE):(is_bool($value)?($value?'Ya':'Tidak'):($value??'Kosong')) }}</dd></div>@endforeach</dl>@endif
        <div class="audit-metadata"><span>IP: {{ $auditService->maskIp($log->ip_address) }}</span>@if($log->user_agent)<span title="{{ $log->user_agent }}">{{ str($log->user_agent)->limit(55) }}</span>@endif</div>
    </div>
</article>
@empty
<section class="panel audit-empty" role="status"><span aria-hidden="true">⌕</span><h3>{{ __('ui.tiada_log_audit_ditemui.1cc11da0') }}</h3><p>{{ __('ui.cuba_ubah_penapis_atau_kosongkan_carian_semasa.1d3f4cbe') }}</p>@if(request()->query())<a class="btn" href="{{ route('admin.audit') }}">{{ __('ui.kosongkan_penapis.3bca173e') }}</a>@endif</section>
@endforelse
</section>
<div class="pagination">{{ $logs->links('components.pagination') }}</div>
@endsection
