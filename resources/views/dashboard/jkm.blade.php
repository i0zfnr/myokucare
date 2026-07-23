@extends('layout', ['title' => 'Dashboard Pegawai JKM'])

@section('content')
<div class="jkm-dashboard">
    <div class="page-head">
        <div>
            <p class="eyebrow">Operasi JKM</p>
            <h2>Selamat datang, Pegawai JKM</h2>
            <p>Pantau rekod OKU, bantuan kebajikan dan aktiviti pekerjaan dalam satu paparan.</p>
        </div>
        <div class="page-actions">
            <button class="btn dashboard-refresh" type="button" data-dashboard-refresh aria-label="Muat semula statistik dashboard">
                <span aria-hidden="true">↻</span><span data-refresh-label>Muat Semula</span>
            </button>
            <a class="btn btn-primary" href="{{ route('oku.create') }}">Daftar OKU Baharu</a>
        </div>
    </div>

    <section class="metric-grid professional-metrics" aria-label="Statistik operasi JKM"
        aria-live="polite" aria-busy="false"
        data-live-dashboard data-statistics-url="{{ route('dashboard.statistics') }}">
        @foreach($metrics as $metric)
            <article class="metric-card metric-{{ $metric['tone'] }}" aria-label="{{ $metric['label'] }}: {{ number_format($metric['value']) }}">
                <div class="metric-top">
                    <span class="metric-icon" aria-hidden="true"><x-dashboard-icon :name="$metric['icon']" /></span>
                    <span class="metric-status"><i aria-hidden="true"></i> Data semasa</span>
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
                    <span class="panel-kicker">Keutamaan hari ini</span>
                    <h3>Permohonan Memerlukan Tindakan</h3>
                    <p>Permohonan berstatus Pending atau Under Review</p>
                </div>
                <a class="panel-action" href="{{ route('welfare.index') }}">Lihat semua <span aria-hidden="true">→</span></a>
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
                        <strong>Semua urusan selesai</strong>
                        <p>Tiada permohonan tertunda buat masa ini.</p>
                    </div>
                @endforelse
            </div>
        </article>

        <aside class="panel professional-panel">
            <div class="panel-head">
                <div>
                    <span class="panel-kicker">Jadual susulan</span>
                    <h3>Kaji Semula Akan Datang</h3>
                    <p>Temu janji yang perlu diberi perhatian</p>
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
                        <strong>Tiada jadual terdekat</strong>
                        <p>Jadual baharu akan dipaparkan di sini.</p>
                    </div>
                @endforelse
            </div>
        </aside>
    </section>

    @include('dashboard.partials.live-oku-statistics')
</div>
@endsection
