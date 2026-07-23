@extends('layout',['title'=>'Dashboard Pengguna OKU'])
@section('content')
@php
    $profileFields=$oku?collect(['name','ic_number','oku_card_number','oku_category','phone_number','education_level','career_summary','skills','oku_card_image_path'])->filter(fn($field)=>filled($oku->{$field})):collect();
    $profilePercent=$oku?(int)round(($profileFields->count()/9)*100):0;
    $verificationLabels=['Pending'=>'Menunggu pengesahan','Verified'=>'Kad OKU disahkan','Rejected'=>'Perlu pembetulan'];
    $interestLabels=['Interested'=>'Minat direkodkan','Applied'=>'Telah memohon','Shortlisted'=>'Disenarai pendek','Interviewed'=>'Temu duga','Hired'=>'Berjaya ditempatkan','Rejected'=>'Tidak berjaya'];
    $metrics=[
        ['label'=>'Cadangan Kerja','value'=>$matchingJobs->count(),'icon'=>'job-search','tone'=>'coral','caption'=>'Padanan untuk profil anda'],
        ['label'=>'Permohonan Kerja','value'=>$interests->count(),'icon'=>'briefcase','tone'=>'amber','caption'=>'Aktiviti pekerjaan'],
        ['label'=>'Permohonan Kebajikan','value'=>$welfareApplications->count(),'icon'=>'welfare','tone'=>'purple','caption'=>'Rekod bantuan anda'],
        ['label'=>'Pekerjaan Aktif','value'=>$activeEmployment?1:0,'icon'=>'employment-report','tone'=>'green','caption'=>$activeEmployment?($activeEmployment->job?->title??'Penempatan semasa'):'Belum ditempatkan'],
    ];
@endphp
<div class="oku-user-dashboard">
<div class="page-head">
    <div><p class="eyebrow">Ruang Peribadi</p><h2>Selamat datang, {{ $oku?->name ?? auth()->user()->name }}</h2><p>Temui pekerjaan yang sesuai dan pantau perkembangan permohonan anda.</p></div>
    <div class="page-actions"><a class="btn" href="{{ route('career-profile.show') }}">{{ $oku?'Kemaskini Profil':'Lengkapkan Profil' }}</a><a class="btn btn-primary" href="{{ route('jobs.index') }}">Cari Peluang Kerja</a></div>
</div>

<section class="metric-grid professional-metrics oku-user-metrics" aria-label="Ringkasan peribadi">
@foreach($metrics as $metric)
<article class="metric-card metric-{{ $metric['tone'] }}">
    <div class="metric-top"><span class="metric-icon" aria-hidden="true"><x-dashboard-icon :name="$metric['icon']"/></span><span class="metric-status"><i></i>Peribadi</span></div>
    <div class="metric-content"><span>{{ $metric['label'] }}</span><strong>{{ number_format($metric['value']) }}</strong><small>{{ $metric['caption'] }}</small></div>
</article>
@endforeach
</section>

<section class="oku-user-grid">
    <article class="panel professional-panel oku-job-panel">
        <div class="panel-head"><div><p class="panel-kicker">Padanan Kerjaya</p><h3>Cadangan Pekerjaan untuk Anda</h3><p>Jawatan aktif berdasarkan kategori, lokasi dan profil kerjaya.</p></div><a class="panel-action" href="{{ route('jobs.index') }}">Lihat semua →</a></div>
        <div class="oku-job-list">
        @forelse($matchingJobs as $job)
            <a href="{{ route('jobs.index',['search'=>$job->title]) }}" class="oku-job-row">
                <span class="oku-job-icon" aria-hidden="true"><x-dashboard-icon name="briefcase"/></span>
                <span><strong>{{ $job->title }}</strong><small>{{ $job->employer->company_name }} · {{ $job->location }}</small></span>
                <span class="match-score"><b>{{ $job->match_score }}%</b> padanan</span>
            </a>
        @empty
            <div class="panel-empty"><span aria-hidden="true">⌕</span><strong>{{ $oku?'Tiada cadangan buat masa ini':'Profil belum lengkap' }}</strong><p>{{ $oku?'Jawatan baharu yang sepadan akan muncul di sini.':'Lengkapkan profil untuk menerima padanan pekerjaan.' }}</p></div>
        @endforelse
        </div>
    </article>

    <aside class="panel professional-panel oku-profile-panel">
        <div class="panel-head"><div><p class="panel-kicker">Profil Anda</p><h3>Status Profil</h3><p>Kelengkapan dan pengesahan akaun.</p></div><span class="panel-count">{{ $profilePercent }}%</span></div>
        <div class="oku-profile-progress"><div><i style="width:{{ $profilePercent }}%"></i></div><span>{{ $profilePercent<100?'Lengkapkan profil untuk padanan lebih tepat.':'Profil anda telah lengkap.' }}</span></div>
        <div class="oku-verification-status status-{{ strtolower($oku?->verification_status??'pending') }}"><span aria-hidden="true"><x-dashboard-icon name="id-card"/></span><div><strong>{{ $verificationLabels[$oku?->verification_status??'Pending'] }}</strong><small>{{ $oku?->oku_card_number??'Nombor Kad OKU belum dilengkapkan' }}</small></div></div>
        <a class="btn" href="{{ route('career-profile.show') }}">Semak Profil Kerjaya</a>
    </aside>
</section>

<section class="oku-user-lower-grid">
    <article class="panel professional-panel">
        <div class="panel-head"><div><p class="panel-kicker">Perkembangan</p><h3>Aktiviti Pekerjaan</h3><p>Status minat dan permohonan terkini.</p></div></div>
        <div class="activity-list">@forelse($interests->take(4) as $interest)<div class="activity-row"><span class="metric-icon activity-icon" aria-hidden="true"><x-dashboard-icon name="briefcase"/></span><div><strong>{{ $interest->job->title }}</strong><span>{{ $interest->job->employer->company_name }}</span></div><span class="badge">{{ $interestLabels[$interest->status]??$interest->status }}</span></div>@empty<div class="panel-empty compact"><strong>Belum ada aktiviti pekerjaan</strong><p>Jawatan yang anda minati akan dipaparkan di sini.</p></div>@endforelse</div>
    </article>
    <article class="panel professional-panel">
        <div class="panel-head"><div><p class="panel-kicker">Sokongan</p><h3>Permohonan Kebajikan</h3><p>Status permohonan bantuan terkini.</p></div><a class="panel-action" href="{{ route('welfare.index') }}">Lihat semua →</a></div>
        <div class="activity-list">@forelse($welfareApplications->take(4) as $application)<div class="activity-row"><span class="metric-icon activity-icon" aria-hidden="true"><x-dashboard-icon name="welfare"/></span><div><strong>{{ $application->application_type }}</strong><span>{{ $application->application_date->format('d M Y') }}</span></div><span class="badge">{{ $application->status }}</span></div>@empty<div class="panel-empty compact"><strong>Tiada permohonan kebajikan</strong><p>Permohonan baharu boleh dibuat melalui menu Kebajikan.</p></div>@endforelse</div>
    </article>
</section>
</div>
@endsection
