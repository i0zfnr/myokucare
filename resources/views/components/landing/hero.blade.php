<section class="welcome-hero hero-gradient-shift" aria-labelledby="hero-heading">
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>
    <div class="hero-blob hero-blob-3"></div>
    <div class="hero-inner">
        <div class="hero-copy hero-fade-group">
            <span class="hero-tag">{{ __('ui.prototaip_digital_pekerjaan_inklusif_kebajikan.65b4e0b1') }}</span>
            <div class="hero-system-name">MyOKUcare</div>
            <h1 id="hero-heading">{{ __('ui.memperkasakan_komuniti_oku_melalui_pekerjaan_dan_sokongan.94f49c37') }}</h1>
            <p>{{ __('ui.myokucare_ialah_prototaip_platform_digital_yang_menyatukan.a78c73b4') }}</p>
            <div class="hero-buttons">
                <a class="btn primary-light btn-ripple" href="{{ route('register') }}">{{ __('ui.daftar_percuma.64463f2b') }}</a>
                <a class="btn outline-light btn-ripple btn-ripple-outline" href="{{ route('login') }}">{{ __('ui.log_masuk.65586411') }}</a>
            </div>
            <div class="pwa-hero-roles" aria-label="{{ __('ui.peranan_pengguna.accb696c') }}">
                <span><x-dashboard-icon name="users"/> {{ __('ui.oku_waris.ac8bacf5') }}</span>
                <span><x-dashboard-icon name="briefcase"/> {{ __('ui.majikan.73adc28e') }}</span>
                <span><x-dashboard-icon name="government"/> {{ __('ui.pegawai_jkm.31c195cb') }}</span>
            </div>
            <p class="hero-account-note">{{ __('ui.pendaftaran_awam_tersedia_untuk_individu_oku_dan.72ac1214') }}</p>
            <div class="pwa-hero-footer">
                <span>{{ __('ui.direka_untuk_menyokong_perkhidmatan_oku_data_dilindungi.ffb909ac') }}</span>
            </div>
        </div>
        <div class="hero-panel hero-float" aria-hidden="true">
            <div class="preview">
                <div class="preview-top"><span class="dot"></span><span class="dot"></span><span class="dot"></span></div>
                <div class="preview-body">
                    <div class="preview-greeting"></div>
                    <div class="preview-metrics"><div class="preview-card"></div><div class="preview-card"></div><div class="preview-card"></div></div>
                    <div class="preview-match">
                        <div class="match-header">
                            <span class="match-badge">PADANAN PEKERJAAN</span>
                            <span class="match-time">{{ __('ui.baru.5356cfdd') }}</span>
                        </div>
                        <div class="match-body">
                            <div class="match-avatar"></div>
                            <div class="match-info">
                                <strong>{{ __('ui.pembantu_operasi.ce904c98') }}</strong>
                                <span>{{ __('ui.majlis_bandaraya_shah_alam.a48d1577') }}</span>
                                <span class="match-tag">{{ __('ui.sesuai_dengan_profil_anda.0bcdec54') }}</span>
                            </div>
                        </div>
                        <div class="match-skill-row">
                            <span class="match-skill">{{ __('ui.komunikasi.8d01cb54') }}</span>
                            <span class="match-skill">{{ __('ui.kerja_berpasukan.50139eb8') }}</span>
                            <span class="match-skill">{{ __('ui.asas_it.c5ded828') }}</span>
                        </div>
                        <div class="match-footer">
                            <span class="match-match">{{ __('ui.91_padanan.59b7c650') }}</span>
                            <span class="match-action">{{ __('ui.mohon_sekarang.b8827eb8') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
