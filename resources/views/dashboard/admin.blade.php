@extends('layout',['title'=>'Dashboard Pentadbir'])
@section('content')
@php
    $metrics=[
        ['label'=>'Jumlah Pengguna','value'=>$totalUsers,'key'=>'total_users','icon'=>'users','tone'=>'coral','caption'=>'Akaun berdaftar'],
        ['label'=>'Jumlah OKU','value'=>$stats['total'],'key'=>'total','icon'=>'id-card','tone'=>'purple','caption'=>'Profil dalam sistem'],
        ['label'=>'Majikan Berdaftar','value'=>$totalEmployers,'key'=>'total_employers','icon'=>'employer','tone'=>'amber','caption'=>'Organisasi berdaftar'],
        ['label'=>'Jawatan Aktif','value'=>$openJobs,'key'=>'open_jobs','icon'=>'briefcase','tone'=>'green','caption'=>'Peluang sedang dibuka'],
    ];
    $roleLabels=['super_admin'=>'Admin System','jkm_officer'=>'Pegawai JKM','employer'=>'Majikan','oku_user'=>'Pengguna OKU'];
    $roleIcons=['super_admin'=>'settings','jkm_officer'=>'profile','employer'=>'employer','oku_user'=>'id-card'];
@endphp
<div class="page-head admin-dashboard-head">
    <div><p class="eyebrow">{{ __('ui.pentadbiran_sistem.f78f3faf') }}</p><h2>{{ __('ui.selamat_datang_admin_system.afdfda97') }}</h2><p>{{ __('ui.pantau_pengguna_organisasi_dan_operasi_keseluruhan_myokucare.0bc63465') }}</p></div>
    <div class="page-actions"><a class="btn" href="{{ route('admin.users.index') }}">{{ __('ui.urus_pengguna.c50aaa63') }}</a><a class="btn btn-primary" href="{{ route('oku.create') }}">{{ __('ui.daftar_oku_baharu.92f38243') }}</a></div>
</div>

<section class="metric-grid professional-metrics admin-metrics stagger-children" aria-label="{{ __('ui.statistik_pentadbiran.ad50c908') }}" data-live-dashboard data-statistics-url="{{ route('dashboard.statistics') }}">
@foreach($metrics as $metric)
    <article class="metric-card metric-{{ $metric['tone'] }} widget-hover">
        <div class="metric-top"><span class="metric-icon" aria-hidden="true"><x-dashboard-icon :name="$metric['icon']"/></span><span class="metric-status"><i></i>{{ __('ui.data_semasa.d807b88d') }}</span></div>
        <div class="metric-content"><span>{{ $metric['label'] }}</span><strong data-stat="{{ $metric['key'] }}">{{ number_format($metric['value']) }}</strong><small>{{ $metric['caption'] }}</small></div>
    </article>
@endforeach
</section>

<section class="admin-dashboard-grid">
    <article class="panel professional-panel admin-role-panel card-lift">
        <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.akses_sistem.00e3e4bd') }}</p><h3>{{ __('ui.pengguna_mengikut_peranan.32a9a4d6') }}</h3><p>{{ __('ui.taburan_semua_akaun_berdaftar_mengikut_tahap_akses.d92beeaa') }}</p></div><a class="panel-action" href="{{ route('admin.users.index') }}">{{ __('ui.lihat_semua.cff5ba88') }}</a></div>
        <div class="admin-role-list">
        @forelse($roleLabels as $role=>$label)
            @php $total=(int)($roles[$role]??0); $percentage=$totalUsers?round(($total/$totalUsers)*100):0; @endphp
            <a class="admin-role-row" href="{{ route('admin.users.index',['role'=>$role]) }}">
                <span class="admin-role-icon" aria-hidden="true"><x-dashboard-icon :name="$roleIcons[$role]"/></span>
                <span class="admin-role-copy"><strong>{{ $label }}</strong><small>{{ $total }} akaun · {{ $percentage }}%</small><i><b style="width:{{ $percentage }}%"></b></i></span>
                <span class="admin-role-count">{{ $total }}</span>
            </a>
        @empty
            <div class="panel-empty">{{ __('ui.tiada_akaun_pengguna.444507fc') }}</div>
        @endforelse
        </div>
    </article>

    <aside class="panel professional-panel admin-action-panel card-lift">
        <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.pintasan.c1b90977') }}</p><h3>{{ __('ui.tindakan_admin_system.06ef7c51') }}</h3><p>{{ __('ui.akses_pantas_kepada_fungsi_utama_sistem.8398239a') }}</p></div></div>
        <div class="admin-action-list">
            @foreach([
                ['route'=>'admin.users.index','icon'=>'users','title'=>'Pengurusan Pengguna','copy'=>'Urus akaun, peranan dan status akses'],
                ['route'=>'employers.index','icon'=>'employer','title'=>'Urus Majikan','copy'=>'Pantau organisasi dan peluang inklusif'],
                ['route'=>'reports.employment','icon'=>'employment-report','title'=>'Laporan Sistem','copy'=>'Lihat statistik pekerjaan semasa'],
                ['route'=>'admin.audit','icon'=>'audit','title'=>'Audit Aktiviti','copy'=>'Semak perubahan akaun pentadbir'],
            ] as $action)
            <a href="{{ route($action['route']) }}"><span aria-hidden="true"><x-dashboard-icon :name="$action['icon']"/></span><div><strong>{{ $action['title'] }}</strong><small>{{ $action['copy'] }}</small></div><b aria-hidden="true">→</b></a>
            @endforeach
        </div>
    </aside>
</section>

@include('dashboard.partials.live-oku-statistics')
@endsection
