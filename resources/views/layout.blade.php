@php
    $pageTitle = $title ?? 'Dashboard';
    $user = auth()->user();
    $allNav = [
        ['section'=>'Utama','label'=>'Dashboard','route'=>'dashboard','active'=>'dashboard','icon'=>'⌂'],
        ['section'=>'Pengurusan OKU','label'=>'Rekod OKU','route'=>'oku.index','active'=>'oku.index','icon'=>'◉','roles'=>['super_admin','jkm_officer']],
        ['section'=>'Pengurusan OKU','label'=>'Daftar OKU','route'=>'oku.create','active'=>'oku.create','icon'=>'+','roles'=>['super_admin','jkm_officer']],
        ['section'=>'Pekerjaan','label'=>'Majikan','route'=>'employers.index','active'=>'employers.*','icon'=>'▣','roles'=>['super_admin','jkm_officer','employer']],
        ['section'=>'Pekerjaan','label'=>'Peluang Kerja','route'=>'jobs.index','active'=>'jobs.*','icon'=>'◆','roles'=>['super_admin','jkm_officer','employer','oku_user','family_member']],
        ['section'=>'Kebajikan','label'=>'Permohonan','route'=>'welfare.index','active'=>'welfare.*','icon'=>'♡','roles'=>['super_admin','jkm_officer','oku_user','family_member']],
        ['section'=>'Laporan','label'=>'Statistik Pekerjaan','route'=>'reports.employment','active'=>'reports.employment','icon'=>'↗','roles'=>['super_admin','jkm_officer','viewer']],
        ['section'=>'Laporan','label'=>'Statistik Kebajikan','route'=>'reports.welfare','active'=>'reports.welfare','icon'=>'≋','roles'=>['super_admin','jkm_officer','viewer']],
    ];
    $nav = collect($allNav)->filter(fn($item) => !isset($item['roles']) || in_array($user->role, $item['roles'], true));
@endphp
<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main">Terus ke kandungan</a>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="{{ route('dashboard') }}">
            <span class="brand-mark">M</span>
            <span class="brand-copy"><strong>MyOKUcare</strong><small>Sistem Sokongan OKU</small></span>
        </a>
        <div class="sidebar-profile"><span class="avatar">{{ collect(explode(' ',$user->name))->take(2)->map(fn($part)=>strtoupper(substr($part,0,1)))->implode('') }}</span><div><strong>{{ $user->name }}</strong><span>{{ $user->role_label }}</span></div></div>
        <nav class="side-nav" aria-label="Navigasi utama">
            @php $section = null; @endphp
            @foreach($nav as $item)
                @if($section !== $item['section'])<div class="nav-label">{{ $item['section'] }}</div>@php $section=$item['section']; @endphp @endif
                <a class="nav-link {{ request()->routeIs($item['active']) ? 'active' : '' }}" href="{{ route($item['route']) }}"><span class="nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}</a>
            @endforeach
        </nav>
        <div class="sidebar-help"><strong>Perlukan bantuan?</strong><p>Rujuk panduan pengurusan data dan sokongan aksesibiliti.</p><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Log Keluar</button></form></div>
    </aside>
    <div class="backdrop" id="backdrop"></div>
    <div class="main-wrap">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:12px"><button class="menu-btn" id="menuButton" aria-label="Buka menu">☰</button><div class="topbar-copy"><h1>{{ $pageTitle }}</h1><p>Pengurusan komuniti yang inklusif dan tersusun</p></div></div>
            <div class="topbar-actions"><label class="search"><span aria-hidden="true">⌕</span><input type="search" placeholder="Cari dalam sistem..." aria-label="Cari"></label><button class="icon-btn" aria-label="Notifikasi">●</button><span class="avatar">{{ strtoupper(substr($user->name,0,2)) }}</span></div>
        </header>
        <main class="content" id="main">
            @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
            @yield('content')
        </main>
    </div>
</div>
<script>
    const sidebar=document.getElementById('sidebar'),backdrop=document.getElementById('backdrop');
    document.getElementById('menuButton')?.addEventListener('click',()=>{sidebar.classList.toggle('open');backdrop.classList.toggle('open')});
    backdrop?.addEventListener('click',()=>{sidebar.classList.remove('open');backdrop.classList.remove('open')});
</script>
</body></html>
