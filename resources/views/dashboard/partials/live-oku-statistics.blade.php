<article class="panel professional-panel live-stat-panel {{ empty($embedded) ? 'live-stat-standalone' : '' }}" data-category-panel>
    <div class="panel-head">
        <div>
            <span class="panel-kicker">Analitik pendaftaran</span>
            <h3>Taburan Kategori OKU</h3>
            <p>Statistik terus daripada rekod OKU semasa</p>
        </div>
        <div class="live-status"><span class="live-dot" aria-hidden="true"></span><span data-live-label>Data langsung</span></div>
    </div>
    <div class="category-chart" data-category-chart>
        @forelse($stats['categories'] as $category => $total)
            @php $percentage = $stats['total'] > 0 ? round(($total / $stats['total']) * 100) : 0; @endphp
            <div class="category-row">
                <div class="category-label">
                    <strong><span class="category-marker" aria-hidden="true"></span>{{ $category }}</strong>
                    <small><b>{{ number_format($total) }}</b> orang <em>{{ $percentage }}%</em></small>
                </div>
                <div class="progress-track"><span style="width: {{ $percentage }}%"></span></div>
            </div>
        @empty
            <div class="chart-empty"><span aria-hidden="true">◎</span><strong>Belum ada data</strong><p>Statistik akan muncul selepas rekod OKU ditambah.</p></div>
        @endforelse
    </div>
    <p class="live-updated">Kemas kini automatik setiap 10 saat · <span data-live-updated>{{ now()->format('d/m/Y H:i:s') }}</span></p>
</article>
