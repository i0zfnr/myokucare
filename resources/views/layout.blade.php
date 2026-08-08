@php
    $pageTitle = $title ?? __('nav.dashboard');
    $user = auth()->user();
    $preferences = array_merge([
        'font_scale'=>'100','dashboard_refresh_seconds'=>10,'default_page_size'=>15,
        'high_contrast_default'=>false,'compact_sidebar'=>false,'show_help_panel'=>true,
        'email_case_notifications'=>true,
    ], $user->preferences ?? []);
    $allNav = [
        ['section'=>__('nav.main'),'label'=>__('nav.dashboard'),'route'=>'dashboard','active'=>'dashboard','icon'=>'dashboard'],
        ['section'=>__('nav.oku_management'),'label'=>__('nav.oku_records'),'route'=>'oku.index','active'=>'oku.index','icon'=>'id-card','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.oku_management'),'label'=>__('nav.register_oku'),'route'=>'oku.create','active'=>'oku.create','icon'=>'add-record','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.oku_management'),'label'=>__('nav.identity_review'),'route'=>'identity-reviews.index','active'=>'identity-reviews.*','icon'=>'audit','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.my_profile'),'label'=>__('nav.career_profile'),'route'=>'career-profile.show','active'=>'career-profile.*','icon'=>'profile','roles'=>['oku_user']],
        ['section'=>__('nav.employment'),'label'=>$user->hasRole('employer')?__('nav.company_profile'):__('nav.employers'),'route'=>'employers.index','active'=>'employers.*','icon'=>'employer','roles'=>['super_admin','jkm_officer','employer']],
        ['section'=>__('nav.employment'),'label'=>__('nav.jobs'),'route'=>'jobs.index','active'=>'jobs.*','icon'=>'jobs','roles'=>['super_admin','jkm_officer','employer','oku_user']],
        ['section'=>__('nav.employment'),'label'=>__('nav.employment_records'),'route'=>'employments.index','active'=>'employments.*','icon'=>'employment-report','roles'=>['super_admin','jkm_officer','employer','oku_user']],
        ['section'=>__('nav.welfare'),'label'=>__('nav.applications'),'route'=>'welfare.index','active'=>'welfare.*','icon'=>'welfare','roles'=>['super_admin','jkm_officer','oku_user']],
        ['section'=>__('nav.reports'),'label'=>__('nav.employment_statistics'),'route'=>'reports.employment','active'=>'reports.employment','icon'=>'employment-report','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.reports'),'label'=>__('nav.welfare_statistics'),'route'=>'reports.welfare','active'=>'reports.welfare','icon'=>'welfare-report','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.reports'),'label'=>__('nav.exports'),'route'=>'exports.index','active'=>'exports.*','icon'=>'employment-report','roles'=>['super_admin','jkm_officer','employer','oku_user']],
        ['section'=>__('nav.administration'),'label'=>__('nav.administration'),'group'=>'administration','icon'=>'users','roles'=>['super_admin']],
        ['section'=>__('nav.account'),'label'=>__('nav.my_profile'),'route'=>'admin.profile','active'=>'admin.profile*','icon'=>'profile','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.account'),'label'=>__('settings'),'route'=>'admin.settings','active'=>'admin.settings*','icon'=>'settings','roles'=>['super_admin','jkm_officer']],
        ['section'=>__('nav.account'),'label'=>__('language'),'route'=>'language-settings.edit','active'=>'language-settings.*','icon'=>'settings'],
        ['section'=>__('nav.account'),'label'=>__('guideline.nav'),'route'=>'guideline.show','active'=>'guideline.*','icon'=>'audit'],
    ];
    $nav = collect($allNav)->filter(fn($item) => !isset($item['roles']) || in_array($user->role, $item['roles'], true));
    $adminNav = [
        ['label'=>__('nav.all_users'),'route'=>'admin.users.index','icon'=>'users'],
        ['label'=>'Admin System','role'=>'super_admin','icon'=>'settings'],
        ['label'=>'Pegawai JKM','role'=>'jkm_officer','icon'=>'profile'],
        ['label'=>__('role.employer'),'role'=>'employer','icon'=>'employer'],
        ['label'=>__('role.oku_user'),'role'=>'oku_user','icon'=>'id-card'],
        ['label'=>__('nav.activity_audit'),'route'=>'admin.audit','icon'=>'audit'],
        ['label'=>__('nav.feature_controls'),'route'=>'admin.feature-controls.index','icon'=>'settings'],
        ['label'=>__('nav.deleted_records'),'route'=>'deleted-records.index','icon'=>'audit'],
    ];
    $globalSearchRoute = $user->hasRole('super_admin','jkm_officer') ? 'oku.index' : 'jobs.index';
    $unreadNotificationCount = $user->unreadNotifications()->count();
    $pwaNavByRole = [
        'super_admin'=>[
            ['label'=>'Utama','route'=>'dashboard','active'=>'dashboard','icon'=>'dashboard'],
            ['label'=>'Pengguna','route'=>'admin.users.index','active'=>'admin.users.*','icon'=>'users'],
            ['label'=>'Audit','route'=>'admin.audit','active'=>'admin.audit','icon'=>'audit'],
            ['label'=>'Tetapan','route'=>'admin.settings','active'=>'admin.settings*','icon'=>'settings'],
        ],
        'jkm_officer'=>[
            ['label'=>'Utama','route'=>'dashboard','active'=>'dashboard','icon'=>'dashboard'],
            ['label'=>'Kebajikan','route'=>'welfare.index','active'=>'welfare.*','icon'=>'welfare'],
            ['label'=>'Semakan','route'=>'identity-reviews.index','active'=>'identity-reviews.*','icon'=>'audit'],
            ['label'=>'Tetapan','route'=>'admin.settings','active'=>'admin.settings*','icon'=>'settings'],
        ],
        'employer'=>[
            ['label'=>'Utama','route'=>'dashboard','active'=>'dashboard','icon'=>'dashboard'],
            ['label'=>'Jawatan','route'=>'jobs.index','active'=>'jobs.*','icon'=>'jobs'],
            ['label'=>'Pekerja','route'=>'employments.index','active'=>'employments.*','icon'=>'employment-report','permission'=>'employment.view'],
            ['label'=>'Eksport','route'=>'exports.index','active'=>'exports.*','icon'=>'employment-report'],
        ],
        'oku_user'=>[
            ['label'=>'Utama','route'=>'dashboard','active'=>'dashboard','icon'=>'dashboard'],
            ['label'=>'Kerja','route'=>'jobs.index','active'=>'jobs.*','icon'=>'jobs'],
            ['label'=>'Kebajikan','route'=>'welfare.index','active'=>'welfare.*','icon'=>'welfare'],
            ['label'=>'Profil','route'=>'career-profile.show','active'=>'career-profile.*','icon'=>'profile'],
        ],
    ];
    $pwaNav = collect($pwaNavByRole[$user->role] ?? [])->filter(
        fn($item) => !isset($item['permission']) || app(\App\Services\PermissionService::class)->allows($user,$item['permission'])
    );
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" data-default-font-scale="{{ $preferences['font_scale'] }}" data-default-high-contrast="{{ $preferences['high_contrast_default']?'1':'0' }}" data-preferences-version="{{ sha1(json_encode($preferences)) }}" data-dashboard-refresh="{{ $preferences['dashboard_refresh_seconds'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-head')
    <title>{{ $pageTitle }} — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/css/animate.css','resources/js/app.js'])
</head>
<body class="{{ $preferences['compact_sidebar']?'compact-sidebar':'' }}">
<a class="skip-link" href="#main">{{ __('accessibility.skip') }}</a>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <span class="brand-copy"><strong>MyOKUcare</strong><small>{{ __('system.support') }}</small></span>
        </a>
        <div class="sidebar-profile">
            <span class="avatar">{{ collect(explode(' ',$user->name))->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->implode('') }}</span>
            <div class="sidebar-profile-copy"><strong>{{ $user->name }}</strong><span><i aria-hidden="true"></i>{{ $user->role_label }}</span></div>
        </div>
        <nav class="side-nav" aria-label="{{ __('ui.navigasi_utama.869d117f') }}">
            @php $section = null; @endphp
            @foreach($nav as $item)
                @if($section !== $item['section'])
                    <div class="nav-label">{{ $item['section'] }}</div>
                    @php $section=$item['section']; @endphp
                @endif
                @if(isset($item['group']) && $item['group']==='administration')
                    @php $adminOpen=request()->routeIs('admin.users.*','admin.audit'); @endphp
                    <details class="nav-dropdown" @if($adminOpen) open @endif>
                        <summary class="nav-link {{ $adminOpen?'active':'' }}">
                            <span class="nav-icon" aria-hidden="true"><x-dashboard-icon name="users"/></span>
                            <span>{{ __('ui.pentadbiran.9f16a3f6') }}</span><b aria-hidden="true"></b>
                        </summary>
                        <div class="nav-submenu">
                            @foreach($adminNav as $adminItem)
                                @php
                                    $href=isset($adminItem['role'])?route('admin.users.role',$adminItem['role']):route($adminItem['route']);
                                    $subActive=isset($adminItem['role'])
                                        ? request()->routeIs('admin.users.role')&&request()->route('role')===$adminItem['role']
                                        : request()->routeIs($adminItem['route']);
                                @endphp
                                <a class="{{ $subActive?'active':'' }}" href="{{ $href }}" @if($subActive) aria-current="page" @endif>
                                    <span aria-hidden="true"><x-dashboard-icon :name="$adminItem['icon']"/></span>{{ $adminItem['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </details>
                    @continue
                @endif
                @php $isActive=request()->routeIs($item['active']); @endphp
                <a class="nav-link {{ $isActive?'active':'' }}" href="{{ route($item['route']) }}" @if($isActive) aria-current="page" @endif><span class="nav-icon" aria-hidden="true"><x-dashboard-icon :name="$item['icon']"/></span><span>{{ $item['label'] }}</span></a>
            @endforeach
        </nav>
        @if($preferences['show_help_panel'])
            <div class="sidebar-help"><span class="sidebar-help-icon" aria-hidden="true">?</span><div><strong>{{ __('accessibility.help_title') }}</strong><p>{{ __('accessibility.help_text') }}</p><a href="{{ route('guideline.show', ['replay' => 1]) }}">{{ __('guideline.replay') }}</a></div></div>
        @endif
        <form class="sidebar-logout" method="post" action="{{ route('logout') }}">@csrf<button type="submit"><span aria-hidden="true">↪</span>{{ __('ui.log_keluar.506f8c0d') }}</button></form>
    </aside>
    <div class="backdrop" id="backdrop"></div>
    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left"><button class="menu-btn" id="menuButton" aria-label="{{ __('ui.buka_menu.79b60505') }}" aria-controls="sidebar" aria-expanded="false">☰</button><div class="topbar-copy"><h1>{{ $pageTitle }}</h1><p>{{ __('ui.pengurusan_komuniti_yang_inklusif_dan_tersusun.f04b24fe') }}</p></div></div>
            <div class="topbar-actions">
                @unless(!$user->hasRole('super_admin','jkm_officer','employer','oku_user'))
                <form class="search" method="get" action="{{ route($globalSearchRoute) }}" role="search"><span aria-hidden="true">⌕</span><input name="search" type="search" maxlength="100" placeholder="{{ $user->hasRole('super_admin','jkm_officer')?'Cari rekod OKU...':'Cari peluang kerja...' }}" aria-label="{{ $user->hasRole('super_admin','jkm_officer')?'Cari rekod OKU':'Cari peluang kerja' }}"></form>
                @endunless
                <div class="accessibility-tools" aria-label="{{ __('ui.tetapan_paparan.caee2a4d') }}">
                    <button class="tool-btn" type="button" data-font-action="decrease" aria-label="{{ __('ui.kecilkan_saiz_teks.0f2ef075') }}">A−</button>
                    <button class="tool-btn" type="button" data-font-action="increase" aria-label="{{ __('ui.besarkan_saiz_teks.27358982') }}">A+</button>
                    <button class="tool-btn" type="button" data-contrast-toggle aria-label="{{ __('ui.tukar_mod_kontras_tinggi.136429a0') }}" aria-pressed="false">◐</button>
                </div>
                <a class="icon-btn notification-button {{ request()->routeIs('notifications.*')?'active':'' }}" href="{{ route('notifications.index') }}" aria-label="{{ __('notifications.page_title') }}{{ $unreadNotificationCount ? ' ('.$unreadNotificationCount.')' : '' }}">
                    <span aria-hidden="true">●</span>
                    @if($unreadNotificationCount)<b>{{ min($unreadNotificationCount, 99) }}</b>@endif
                </a><span class="avatar">{{ strtoupper(substr($user->name,0,2)) }}</span>
            </div>
        </header>
        <main class="content" id="main">
            @if(session('success'))<div class="notice" role="status" aria-live="polite">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
        <nav class="pwa-bottom-nav" aria-label="{{ __('ui.navigasi_aplikasi_mudah_alih.b3f8f717') }}" style="--pwa-nav-items:{{ count($pwaNav) }}">
            @foreach($pwaNav as $item)
                @php $isPwaActive=request()->routeIs($item['active']); @endphp
                <a href="{{ route($item['route']) }}" class="{{ $isPwaActive?'active':'' }}" @if($isPwaActive) aria-current="page" @endif>
                    <span aria-hidden="true"><x-dashboard-icon :name="$item['icon']"/></span>
                    <small>{{ $item['label'] }}</small>
                </a>
            @endforeach
        </nav>
    </div>
</div>
<script>
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('backdrop');
    const menuButton = document.getElementById('menuButton');
    const setSidebarOpen = (open) => {
        sidebar?.classList.toggle('open', open);
        backdrop?.classList.toggle('open', open);
        menuButton?.setAttribute('aria-expanded', String(open));
        document.body.classList.toggle('sidebar-open', open);
    };
    menuButton?.addEventListener('click', () => setSidebarOpen(!document.body.classList.contains('sidebar-open')));
    backdrop?.addEventListener('click', () => setSidebarOpen(false));
    sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setSidebarOpen(false)));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('sidebar-open')) {
            setSidebarOpen(false);
            menuButton?.focus();
        }
    });
    const standaloneQuery = window.matchMedia('(display-mode: standalone)');
    const syncStandaloneMode = () => {
        document.documentElement.classList.toggle('is-standalone', standaloneQuery.matches || window.navigator.standalone === true);
    };
    syncStandaloneMode();
    standaloneQuery.addEventListener?.('change', syncStandaloneMode);
</script>
</body></html>
