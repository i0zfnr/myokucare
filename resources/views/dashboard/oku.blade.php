@extends('layout',['title'=>__('dashboard.oku.title')])
@section('content')
@php
    $profileFields=$oku?collect(['name','ic_number','oku_card_number','oku_category','phone_number','education_level','career_summary','skills','oku_card_image_path'])->filter(fn($field)=>filled($oku->{$field})):collect();
    $profilePercent=$oku?(int)round(($profileFields->count()/9)*100):0;
    $verificationLabels=['Pending'=>__('dashboard.oku.verification_pending'),'Verified'=>__('dashboard.oku.verification_verified'),'Rejected'=>__('dashboard.oku.verification_rejected')];
    $interestLabels=['Interested'=>__('dashboard.oku.interest_interested'),'Applied'=>__('dashboard.oku.interest_applied'),'Shortlisted'=>__('dashboard.oku.interest_shortlisted'),'Interviewed'=>__('dashboard.oku.interest_interviewed'),'Hired'=>__('dashboard.oku.interest_hired'),'Rejected'=>__('dashboard.oku.interest_rejected')];
    $metrics=[
        ['label'=>__('dashboard.oku.job_recommendations'),'value'=>$matchingJobs->count(),'icon'=>'job-search','tone'=>'coral','caption'=>__('dashboard.oku.profile_matches')],
        ['label'=>__('dashboard.oku.job_applications'),'value'=>$interests->count(),'icon'=>'briefcase','tone'=>'amber','caption'=>__('dashboard.oku.employment_activity')],
        ['label'=>__('dashboard.oku.welfare_applications'),'value'=>$welfareApplications->count(),'icon'=>'welfare','tone'=>'purple','caption'=>__('dashboard.oku.assistance_records')],
        ['label'=>__('dashboard.oku.active_employment'),'value'=>$activeEmployment?1:0,'icon'=>'employment-report','tone'=>'green','caption'=>$activeEmployment?($activeEmployment->job?->title??__('dashboard.oku.current_placement')):__('dashboard.oku.not_placed')],
    ];
@endphp
<div class="oku-user-dashboard">
<div class="page-head">
    <div><p class="eyebrow">{{ __('ui.ruang_peribadi.969085d6') }}</p><h2>{{ __('dashboard.oku.welcome', ['name' => $oku?->name ?? auth()->user()->name]) }}</h2><p>{{ __('ui.temui_pekerjaan_yang_sesuai_dan_pantau_perkembangan.a059e0f9') }}</p></div>
    <div class="page-actions"><a class="btn" href="{{ route('career-profile.show') }}">{{ $oku?__('dashboard.oku.update_profile'):__('dashboard.oku.complete_profile') }}</a><a class="btn btn-primary" href="{{ route('jobs.index') }}">{{ __('ui.cari_peluang_kerja.4672ccd4') }}</a></div>
</div>

<section class="metric-grid professional-metrics oku-user-metrics" aria-label="{{ __('ui.ringkasan_peribadi.e1854ba7') }}">
@foreach($metrics as $metric)
<article class="metric-card metric-{{ $metric['tone'] }}">
    <div class="metric-top"><span class="metric-icon" aria-hidden="true"><x-dashboard-icon :name="$metric['icon']"/></span><span class="metric-status"><i></i>{{ __('ui.peribadi.1f9838fb') }}</span></div>
    <div class="metric-content"><span>{{ $metric['label'] }}</span><strong>{{ number_format($metric['value']) }}</strong><small>{{ $metric['caption'] }}</small></div>
</article>
@endforeach
</section>

<section class="oku-user-grid">
    <article class="panel professional-panel oku-job-panel">
        <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.padanan_kerjaya.ccbc82cc') }}</p><h3>{{ __('ui.cadangan_pekerjaan_untuk_anda.d00cd342') }}</h3><p>{{ __('ui.jawatan_aktif_berdasarkan_kategori_lokasi_dan_profil.d3066fc4') }}</p></div><a class="panel-action" href="{{ route('jobs.index') }}">{{ __('ui.lihat_semua.cff5ba88') }}</a></div>
        <div class="oku-job-list">
        @forelse($matchingJobs as $job)
            <a href="{{ route('jobs.index',['search'=>$job->title]) }}" class="oku-job-row">
                <span class="oku-job-icon" aria-hidden="true"><x-dashboard-icon name="briefcase"/></span>
                <span><strong>{{ $job->title }}</strong><small>{{ $job->employer->company_name }} · {{ $job->location }}</small></span>
                <span class="match-score"><b>{{ $job->match_score }}%</b> {{ __('ui.padanan.d37265e3') }}</span>
            </a>
        @empty
            <div class="panel-empty"><span aria-hidden="true">⌕</span><strong>{{ $oku?__('dashboard.oku.no_recommendations'):__('dashboard.oku.profile_incomplete') }}</strong><p>{{ $oku?__('dashboard.oku.new_matches_here'):__('dashboard.oku.complete_for_matches') }}</p></div>
        @endforelse
        </div>
    </article>

    <aside class="panel professional-panel oku-profile-panel">
        <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.profil_anda.6cdc9709') }}</p><h3>{{ __('ui.status_profil.d5cd3724') }}</h3><p>{{ __('ui.kelengkapan_dan_pengesahan_akaun.b35710ae') }}</p></div><span class="panel-count">{{ $profilePercent }}%</span></div>
        <div class="oku-profile-progress"><div><i style="width:{{ $profilePercent }}%"></i></div><span>{{ $profilePercent<100?__('dashboard.oku.complete_more_accurate'):__('dashboard.oku.profile_complete') }}</span></div>
        <div class="oku-verification-status status-{{ strtolower($oku?->verification_status??'pending') }}"><span aria-hidden="true"><x-dashboard-icon name="id-card"/></span><div><strong>{{ $verificationLabels[$oku?->verification_status??'Pending'] }}</strong><small>{{ $oku?->oku_card_number??__('dashboard.oku.card_number_missing') }}</small></div></div>
        <a class="btn" href="{{ route('career-profile.show') }}">{{ __('ui.semak_profil_kerjaya.68981fc5') }}</a>
    </aside>
</section>

<section class="oku-user-lower-grid">
    <article class="panel professional-panel">
        <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.perkembangan.bf066827') }}</p><h3>{{ __('ui.aktiviti_pekerjaan.3fb08548') }}</h3><p>{{ __('ui.status_minat_dan_permohonan_terkini.2e3a0894') }}</p></div></div>
        <div class="activity-list">@forelse($interests->take(4) as $interest)<div class="activity-row"><span class="metric-icon activity-icon" aria-hidden="true"><x-dashboard-icon name="briefcase"/></span><div><strong>{{ $interest->job->title }}</strong><span>{{ $interest->job->employer->company_name }}</span></div><span class="badge">{{ $interestLabels[$interest->status]??$interest->status }}</span></div>@empty<div class="panel-empty compact"><strong>{{ __('ui.belum_ada_aktiviti_pekerjaan.85e5d2ac') }}</strong><p>{{ __('ui.jawatan_yang_anda_minati_akan_dipaparkan_di.2ed7f4e7') }}</p></div>@endforelse</div>
    </article>
    <article class="panel professional-panel">
        <div class="panel-head"><div><p class="panel-kicker">{{ __('ui.sokongan.8079b0e9') }}</p><h3>{{ __('ui.permohonan_kebajikan.33bca6ce') }}</h3><p>{{ __('ui.status_permohonan_bantuan_terkini.260a576c') }}</p></div><a class="panel-action" href="{{ route('welfare.index') }}">{{ __('ui.lihat_semua.cff5ba88') }}</a></div>
        <div class="activity-list">@forelse($welfareApplications->take(4) as $application)<div class="activity-row"><span class="metric-icon activity-icon" aria-hidden="true"><x-dashboard-icon name="welfare"/></span><div><strong>{{ $application->application_type }}</strong><span>{{ $application->application_date->format('d M Y') }}</span></div><span class="badge">{{ $application->status }}</span></div>@empty<div class="panel-empty compact"><strong>{{ __('ui.tiada_permohonan_kebajikan.df65e29e') }}</strong><p>{{ __('ui.permohonan_baharu_boleh_dibuat_melalui_menu_kebajikan.58ad53b0') }}</p></div>@endforelse</div>
    </article>
</section>
</div>
@endsection
