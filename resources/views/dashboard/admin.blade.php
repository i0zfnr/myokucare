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
    <div><p class="eyebrow">Pentadbiran Sistem</p><h2>Selamat datang, Admin System</h2><p>Pantau pengguna, organisasi dan operasi keseluruhan MyOKUcare.</p></div>
    <div class="page-actions"><a class="btn" href="{{ route('admin.users.index') }}">Urus Pengguna</a><a class="btn btn-primary" href="{{ route('oku.create') }}">Daftar OKU Baharu</a></div>
</div>

<section class="metric-grid professional-metrics admin-metrics stagger-children" aria-label="Statistik pentadbiran" data-live-dashboard data-statistics-url="{{ route('dashboard.statistics') }}">
@foreach($metrics as $metric)
    <article class="metric-card metric-{{ $metric['tone'] }} widget-hover">
        <div class="metric-top"><span class="metric-icon" aria-hidden="true"><x-dashboard-icon :name="$metric['icon']"/></span><span class="metric-status"><i></i>Data semasa</span></div>
        <div class="metric-content"><span>{{ $metric['label'] }}</span><strong data-stat="{{ $metric['key'] }}">{{ number_format($metric['value']) }}</strong><small>{{ $metric['caption'] }}</small></div>
    </article>
@endforeach
</section>

<section class="admin-dashboard-grid">
    <article class="panel professional-panel admin-role-panel card-lift">
        <div class="panel-head"><div><p class="panel-kicker">Akses Sistem</p><h3>Pengguna Mengikut Peranan</h3><p>Taburan semua akaun berdaftar mengikut tahap akses.</p></div><a class="panel-action" href="{{ route('admin.users.index') }}">Lihat semua →</a></div>
        <div class="admin-role-list">
        @forelse($roleLabels as $role=>$label)
            @php $total=(int)($roles[$role]??0); $percentage=$totalUsers?round(($total/$totalUsers)*100):0; @endphp
            <a class="admin-role-row" href="{{ route('admin.users.index',['role'=>$role]) }}">
                <span class="admin-role-icon" aria-hidden="true"><x-dashboard-icon :name="$roleIcons[$role]"/></span>
                <span class="admin-role-copy"><strong>{{ $label }}</strong><small>{{ $total }} akaun · {{ $percentage }}%</small><i><b style="width:{{ $percentage }}%"></b></i></span>
                <span class="admin-role-count">{{ $total }}</span>
            </a>
        @empty
            <div class="panel-empty">Tiada akaun pengguna.</div>
        @endforelse
        </div>
    </article>

    <aside class="panel professional-panel admin-action-panel card-lift">
        <div class="panel-head"><div><p class="panel-kicker">Pintasan</p><h3>Tindakan Admin System</h3><p>Akses pantas kepada fungsi utama sistem.</p></div></div>
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
