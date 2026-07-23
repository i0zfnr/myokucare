@php
    $pageTitle = $title ?? 'Dashboard';
    $user = auth()->user();
    $preferences = array_merge([
        'font_scale'=>'100','dashboard_refresh_seconds'=>10,'default_page_size'=>15,
        'high_contrast_default'=>false,'compact_sidebar'=>false,'show_help_panel'=>true,
        'email_case_notifications'=>true,
    ], $user->preferences ?? []);
    $allNav = [
        ['section'=>'Utama','label'=>'Dashboard','route'=>'dashboard','active'=>'dashboard','icon'=>'dashboard'],
        ['section'=>'Pengurusan OKU','label'=>'Rekod OKU','route'=>'oku.index','active'=>'oku.index','icon'=>'id-card','roles'=>['super_admin','jkm_officer']],
        ['section'=>'Pengurusan OKU','label'=>'Daftar OKU','route'=>'oku.create','active'=>'oku.create','icon'=>'add-record','roles'=>['super_admin','jkm_officer']],
        ['section'=>'Profil Saya','label'=>'Profil Kerjaya','route'=>'career-profile.show','active'=>'career-profile.*','icon'=>'profile','roles'=>['oku_user']],
        ['section'=>'Pekerjaan','label'=>'Majikan','route'=>'employers.index','active'=>'employers.*','icon'=>'employer','roles'=>['super_admin','jkm_officer','employer']],
        ['section'=>'Pekerjaan','label'=>'Peluang Kerja','route'=>'jobs.index','active'=>'jobs.*','icon'=>'jobs','roles'=>['super_admin','jkm_officer','employer','oku_user','family_member']],
        ['section'=>'Kebajikan','label'=>'Permohonan','route'=>'welfare.index','active'=>'welfare.*','icon'=>'welfare','roles'=>['super_admin','jkm_officer','oku_user','family_member']],
        ['section'=>'Laporan','label'=>'Statistik Pekerjaan','route'=>'reports.employment','active'=>'reports.employment','icon'=>'employment-report','roles'=>['super_admin','jkm_officer','viewer']],
        ['section'=>'Laporan','label'=>'Statistik Kebajikan','route'=>'reports.welfare','active'=>'reports.welfare','icon'=>'welfare-report','roles'=>['super_admin','jkm_officer','viewer']],
        ['section'=>'Pentadbiran','label'=>'Pengurusan Pengguna','route'=>'admin.users.index','active'=>'admin.users.*','icon'=>'users','roles'=>['super_admin']],
        ['section'=>'Pentadbiran','label'=>'Audit Aktiviti','route'=>'admin.audit','active'=>'admin.audit','icon'=>'audit','roles'=>['super_admin']],
        ['section'=>'Akaun','label'=>'Profil Saya','route'=>'admin.profile','active'=>'admin.profile*','icon'=>'profile','roles'=>['super_admin','jkm_officer']],
        ['section'=>'Akaun','label'=>'Tetapan','route'=>'admin.settings','active'=>'admin.settings*','icon'=>'settings','roles'=>['super_admin','jkm_officer']],
    ];
    $nav = collect($allNav)->filter(fn($item) => !isset($item['roles']) || in_array($user->role, $item['roles'], true));
    $globalSearchRoute = $user->hasRole('super_admin','jkm_officer') ? 'oku.index' : 'jobs.index';
@endphp
<!doctype html>
<html lang="ms" data-default-font-scale="{{ $preferences['font_scale'] }}" data-default-high-contrast="{{ $preferences['high_contrast_default']?'1':'0' }}" data-preferences-version="{{ sha1(json_encode($preferences)) }}" data-dashboard-refresh="{{ $preferences['dashboard_refresh_seconds'] }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.pwa-head')
    <title>{{ $pageTitle }} — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="{{ $preferences['compact_sidebar']?'compact-sidebar':'' }}">
<a class="skip-link" href="#main">Terus ke kandungan</a>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">M</span>
            <span class="brand-copy"><strong>MyOKUcare</strong><small>Sistem Sokongan OKU</small></span>
        </a>
        <div class="sidebar-profile">
            <span class="avatar">{{ collect(explode(' ',$user->name))->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->implode('') }}</span>
            <div class="sidebar-profile-copy"><strong>{{ $user->name }}</strong><span><i aria-hidden="true"></i>{{ $user->role_label }}</span></div>
        </div>
        <nav class="side-nav" aria-label="Navigasi utama">
            @php $section = null; @endphp
            @foreach($nav as $item)
                @if($section !== $item['section'])
                    <div class="nav-label">{{ $item['section'] }}</div>
                    @php $section=$item['section']; @endphp
                @endif
                @php $isActive=request()->routeIs($item['active']); @endphp
                <a class="nav-link {{ $isActive?'active':'' }}" href="{{ route($item['route']) }}" @if($isActive) aria-current="page" @endif><span class="nav-icon" aria-hidden="true"><x-dashboard-icon :name="$item['icon']"/></span><span>{{ $item['label'] }}</span></a>
            @endforeach
        </nav>
        @if($preferences['show_help_panel'])
            <div class="sidebar-help"><span class="sidebar-help-icon" aria-hidden="true">?</span><div><strong>Perlukan bantuan?</strong><p>Rujuk panduan sistem dan sokongan aksesibiliti.</p></div></div>
        @endif
        <form class="sidebar-logout" method="post" action="{{ route('logout') }}">@csrf<button type="submit"><span aria-hidden="true">↪</span>Log Keluar</button></form>
    </aside>
    <div class="backdrop" id="backdrop"></div>
    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left"><button class="menu-btn" id="menuButton" aria-label="Buka menu" aria-controls="sidebar" aria-expanded="false">☰</button><div class="topbar-copy"><h1>{{ $pageTitle }}</h1><p>Pengurusan komuniti yang inklusif dan tersusun</p></div></div>
            <div class="topbar-actions">
                @unless($user->role==='viewer')
                <form class="search" method="get" action="{{ route($globalSearchRoute) }}" role="search"><span aria-hidden="true">⌕</span><input name="search" type="search" maxlength="100" placeholder="{{ $user->hasRole('super_admin','jkm_officer')?'Cari rekod OKU...':'Cari peluang kerja...' }}" aria-label="{{ $user->hasRole('super_admin','jkm_officer')?'Cari rekod OKU':'Cari peluang kerja' }}"></form>
                @endunless
                <div class="accessibility-tools" aria-label="Tetapan paparan">
                    <button class="tool-btn" type="button" data-font-action="decrease" aria-label="Kecilkan saiz teks">A−</button>
                    <button class="tool-btn" type="button" data-font-action="increase" aria-label="Besarkan saiz teks">A+</button>
                    <button class="tool-btn" type="button" data-contrast-toggle aria-label="Tukar mod kontras tinggi" aria-pressed="false">◐</button>
                </div>
                <button class="icon-btn" aria-label="Notifikasi">●</button><span class="avatar">{{ strtoupper(substr($user->name,0,2)) }}</span>
            </div>
        </header>
        <main class="content" id="main">
            @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script>
    const sidebar=document.getElementById('sidebar'),backdrop=document.getElementById('backdrop'),menuButton=document.getElementById('menuButton');
    const setSidebarOpen=(open)=>{sidebar.classList.toggle('open',open);backdrop.classList.toggle('open',open);menuButton?.setAttribute('aria-expanded',String(open));document.body.classList.toggle('sidebar-open',open)};
    menuButton?.addEventListener('click',()=>setSidebarOpen(!sidebar.classList.contains('open')));
    backdrop?.addEventListener('click',()=>setSidebarOpen(false));
    sidebar?.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>setSidebarOpen(false)));
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&sidebar.classList.contains('open')){setSidebarOpen(false);menuButton?.focus()}});
</script>
</body></html>
