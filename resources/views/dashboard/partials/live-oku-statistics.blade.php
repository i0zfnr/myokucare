<article class="panel professional-panel live-stat-panel {{ empty($embedded) ? 'live-stat-standalone' : '' }}" data-category-panel>
    <div class="panel-head">
        <div>
            <span class="panel-kicker">{{ __('ui.analitik_pendaftaran.d0a31ffe') }}</span>
            <h3>{{ __('ui.taburan_kategori_oku.b9a935e0') }}</h3>
            <p>{{ __('ui.statistik_terus_daripada_rekod_oku_semasa.d0b0698b') }}</p>
        </div>
        <div class="live-status"><span class="live-dot" aria-hidden="true"></span><span data-live-label>{{ __('ui.data_langsung.37f74122') }}</span></div>
    </div>
    <div class="category-chart" data-category-chart>
        @forelse($stats['categories'] as $category => $total)
            @php $percentage = $stats['total'] > 0 ? round(($total / $stats['total']) * 100) : 0; @endphp
            <div class="category-row">
                <div class="category-label">
                    <strong><span class="category-marker" aria-hidden="true"></span>{{ $category }}</strong>
                    <small><b>{{ number_format($total) }}</b> {{ __('ui.orang.3e6c2381') }} <em>{{ $percentage }}%</em></small>
                </div>
                <div class="progress-track"><span style="width: {{ $percentage }}%"></span></div>
            </div>
        @empty
            <div class="chart-empty"><span aria-hidden="true">◎</span><strong>{{ __('ui.belum_ada_data.64302d91') }}</strong><p>{{ __('ui.statistik_akan_muncul_selepas_rekod_oku_ditambah.16d9649a') }}</p></div>
        @endforelse
    </div>
    <p class="live-updated">{{ __('ui.kemas_kini_automatik_setiap_10_saat.088d42be') }} <span data-live-updated>{{ now()->format('d/m/Y H:i:s') }}</span></p>
</article>
