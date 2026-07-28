@extends('layout', ['title' => 'Dashboard Pegawai JKM'])

@section('content')
<div class="jkm-dashboard">
    <div class="page-head">
        <div>
            <p class="eyebrow">{{ __('ui.operasi_jkm.d9343a58') }}</p>
            <h2>{{ __('ui.selamat_datang_pegawai_jkm.3782d8ff') }}</h2>
            <p>{{ __('ui.pantau_rekod_oku_bantuan_kebajikan_dan_aktiviti.6d6403ce') }}</p>
        </div>
        <div class="page-actions">
            <button class="btn dashboard-refresh" type="button" data-dashboard-refresh aria-label="{{ __('ui.muat_semula_statistik_dashboard.87f980a3') }}">
                <span aria-hidden="true">↻</span><span data-refresh-label>{{ __('ui.muat_semula.2d707956') }}</span>
            </button>
            <a class="btn btn-primary" href="{{ route('oku.create') }}">{{ __('ui.daftar_oku_baharu.92f38243') }}</a>
        </div>
    </div>

    <section class="metric-grid professional-metrics" aria-label="{{ __('ui.statistik_operasi_jkm.5b59533e') }}"
        aria-live="polite" aria-busy="false"
        data-live-dashboard data-statistics-url="{{ route('dashboard.statistics') }}">
        @foreach($metrics as $metric)
            <article class="metric-card metric-{{ $metric['tone'] }}" aria-label="{{ $metric['label'] }}: {{ number_format($metric['value']) }}">
                <div class="metric-top">
                    <span class="metric-icon" aria-hidden="true"><x-dashboard-icon :name="$metric['icon']" /></span>
                    <span class="metric-status"><i aria-hidden="true"></i> {{ __('ui.data_semasa.d807b88d') }}</span>
                </div>
                <div class="metric-content">
                    <span>{{ $metric['label'] }}</span>
                    <strong data-stat="{{ $metric['key'] }}">{{ number_format($metric['value']) }}</strong>
                    <small>{{ $metric['caption'] }}</small>
                </div>
            </article>
        @endforeach
    </section>
    <p class="sr-only" role="status" aria-live="polite" data-dashboard-announcement></p>

    <section class="dashboard-grid operational-grid">
        <article class="panel professional-panel">
            <div class="panel-head">
                <div>
                    <span class="panel-kicker">{{ __('ui.keutamaan_hari_ini.be2b32e4') }}</span>
                    <h3>{{ __('ui.permohonan_memerlukan_tindakan.3d76abaf') }}</h3>
                    <p>{{ __('ui.permohonan_berstatus_pending_atau_under_review.eaedd44d') }}</p>
                </div>
                <a class="panel-action" href="{{ route('welfare.index') }}">{{ __('ui.lihat_semua.9527dd65') }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="activity-list">
                @forelse($pendingApplications as $application)
                    <div class="activity-row">
                        <span class="metric-icon activity-icon" aria-hidden="true">♡</span>
                        <div>
                            <strong>{{ $application->oku->name }}</strong>
                            <span>{{ $application->application_type }} · {{ $application->application_date->format('d M Y') }}</span>
                        </div>
                        <span class="badge">{{ $application->status }}</span>
                    </div>
                @empty
                    <div class="panel-empty">
                        <span aria-hidden="true">✓</span>
                        <strong>{{ __('ui.semua_urusan_selesai.3e90b77c') }}</strong>
                        <p>{{ __('ui.tiada_permohonan_tertunda_buat_masa_ini.cbea4a97') }}</p>
                    </div>
                @endforelse
            </div>
        </article>

        <aside class="panel professional-panel">
            <div class="panel-head">
                <div>
                    <span class="panel-kicker">{{ __('ui.jadual_susulan.8ed39794') }}</span>
                    <h3>{{ __('ui.kaji_semula_akan_datang.79bdd0c8') }}</h3>
                    <p>{{ __('ui.temu_janji_yang_perlu_diberi_perhatian.5a3dc93f') }}</p>
                </div>
                <span class="panel-count">{{ $upcomingReviews->count() }}</span>
            </div>
            <div class="activity-list">
                @forelse($upcomingReviews as $review)
                    <div class="activity-row">
                        <span class="metric-icon activity-icon" aria-hidden="true">◷</span>
                        <div>
                            <strong>{{ $review->welfareApplication->oku->name }}</strong>
                            <span>{{ $review->scheduled_date->format('d M Y') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="panel-empty compact">
                        <span aria-hidden="true">◷</span>
                        <strong>{{ __('ui.tiada_jadual_terdekat.2f0ab72c') }}</strong>
                        <p>{{ __('ui.jadual_baharu_akan_dipaparkan_di_sini.9df6adf4') }}</p>
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

    @include('dashboard.partials.live-oku-statistics')
</div>
@endsection
