<!doctype html>
<html lang="{{ app()->getLocale() }}" data-default-font-scale="100" data-default-high-contrast="0" data-preferences-version="0" data-dashboard-refresh="{{ config('app.dashboard_refresh_interval', 10) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MyOKUcare — Prototaip platform digital untuk pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan kebajikan dalam satu sistem mudah dicapai.">
    <meta property="og:title" content="MyOKUcare — Platform Digital Sokongan OKU">
    <meta property="og:description" content="MyOKUcare menyatukan data OKU, pekerjaan, dan kebajikan dalam satu prototaip platform inklusif.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/myokucare-logo.png') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="MyOKUcare — Platform Digital Sokongan OKU">
    <meta name="twitter:description" content="MyOKUcare menyatukan data OKU, pekerjaan, dan kebajikan dalam satu prototaip platform inklusif.">
    @include('partials.pwa-head')
    <title>{{ __('ui.myokucare_platform_digital_sokongan_oku.0b244af7') }}</title>
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main-content">{{ __('ui.langkau_ke_kandungan_utama.2c949d8e') }}</a>
<div class="welcome">
    <x-landing.nav />
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
    <aside class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="{{ __('ui.menu_navigasi.a6d8328e') }}">
        <div class="mobile-drawer-head">
            <div class="drawer-brand">
                <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
                <div>
                    <strong>MyOKUcare</strong>
                    <small>{{ __('ui.platform_digital_sokongan_oku.7eca08df') }}</small>
                </div>
            </div>
            <button class="mobile-drawer-close" type="button" aria-label="{{ __('ui.tutup_menu.7c04e875') }}">✕</button>
        </div>
        <div class="mobile-drawer-body">
            <p class="drawer-section-label">{{ __('ui.halaman_utama.8e008a9a') }}</p>
            <nav class="mobile-drawer-nav" aria-label="{{ __('ui.navigasi_utama.869d117f') }}">
                <a class="mobile-drawer-link" href="#features">
                    <x-dashboard-icon name="dashboard"/>
                    <span>{{ __('ui.perkhidmatan.0b65e05f') }}</span>
                </a>
                <a class="mobile-drawer-link" href="#how-it-works">
                    <x-dashboard-icon name="audit"/>
                    <span>{{ __('ui.cara_berfungsi.f61ac63e') }}</span>
                </a>
                <a class="mobile-drawer-link" href="{{ route('guideline.show') }}">
                    <x-dashboard-icon name="audit"/>
                    <span>{{ __('guideline.nav') }}</span>
                </a>
                <a class="mobile-drawer-link" href="#audience">
                    <x-dashboard-icon name="users"/>
                    <span>{{ __('ui.pengguna.c720f761') }}</span>
                </a>
                <a class="mobile-drawer-link" href="#faq">
                    <x-dashboard-icon name="welfare"/>
                    <span>{{ __('ui.soalan_f_a_q.0c234533') }}</span>
                </a>
            </nav>
            <div class="drawer-divider"></div>
            <p class="drawer-section-label">{{ __('ui.kawalan_paparan.ebb3ad37') }}</p>
            <div class="mobile-drawer-accessibility" role="toolbar" aria-label="{{ __('ui.kawalan_paparan.9b238eb8') }}">
                <button type="button" data-font-action="decrease" aria-label="{{ __('ui.kecilkan_teks.18d9381a') }}">{{ __('ui.a_kecil.049eef2e') }}</button>
                <button type="button" data-font-action="increase" aria-label="{{ __('ui.besarkan_teks.0aa41880') }}">{{ __('ui.a_besar.9cbf5ad9') }}</button>
                <button type="button" data-contrast-toggle aria-label="{{ __('ui.tukar_mod_kontras.d0fb59ea') }}">{{ __('ui.kontras.4ee2296d') }}</button>
            </div>
        </div>
        <div class="mobile-drawer-footer">
            <a class="drawer-btn drawer-btn-login" href="{{ route('login') }}">
                <x-dashboard-icon name="profile"/>
                <span>{{ __('ui.log_masuk.65586411') }}</span>
            </a>
            <a class="drawer-btn drawer-btn-register" href="{{ route('register') }}">
                <x-dashboard-icon name="add-record"/>
                <span>{{ __('ui.daftar_akaun.97f415a6') }}</span>
            </a>
        </div>
    </aside>
    <main id="main-content" tabindex="-1">
        <x-landing.hero />


        <section id="how-it-works" class="steps-section" aria-labelledby="steps-heading">
            <div class="section-title reveal">
                <p class="eyebrow">{{ __('ui.cara_myokucare_berfungsi.d486a131') }}</p>
                <h2 id="steps-heading">{{ __('ui.tiga_langkah_mudah_untuk_bermula.aaf5d51a') }}</h2>
                <p>{{ __('ui.daripada_pendaftaran_hingga_akses_penuh_perkhidmatan_semuanya.14c67f80') }}</p>
            </div>
            <div class="steps-track stagger-children">
                <article class="step-card card-lift card-lift-border">
                    <span class="step-number" aria-hidden="true">1</span>
                    <div class="step-icon card-lift-icon" aria-hidden="true"><x-dashboard-icon name="add-record"/></div>
                    <h3>{{ __('ui.daftar_akaun.97f415a6') }}</h3>
                    <p>{{ __('ui.buat_akaun_percuma_sebagai_individu_oku_atau.aec4904e') }}</p>
                </article>
                <article class="step-card card-lift card-lift-border">
                    <span class="step-number" aria-hidden="true">2</span>
                    <div class="step-icon card-lift-icon" aria-hidden="true"><x-dashboard-icon name="profile"/></div>
                    <h3>{{ __('ui.lengkapkan_profil.3dac4b06') }}</h3>
                    <p>{{ __('ui.isi_maklumat_peribadi_muat_naik_dokumen_sokongan.f73dc139') }}</p>
                </article>
                <article class="step-card card-lift card-lift-border">
                    <span class="step-number" aria-hidden="true">3</span>
                    <div class="step-icon card-lift-icon" aria-hidden="true"><x-dashboard-icon name="dashboard"/></div>
                    <h3>{{ __('ui.akses_perkhidmatan.bbbe2011') }}</h3>
                    <p>{{ __('ui.teroka_peluang_pekerjaan_mohon_bantuan_kebajikan_urus.5a5bdd0c') }}</p>
                </article>
            </div>
        </section>

        <section class="guideline-preview-section" aria-labelledby="guideline-preview-heading">
            <div class="guideline-preview-copy reveal">
                <p class="eyebrow">{{ __('guideline.welcome_eyebrow') }}</p>
                <h2 id="guideline-preview-heading">{{ __('guideline.welcome_section_title') }}</h2>
                <p>{{ __('guideline.welcome_section_text') }}</p>
                <a class="btn btn-primary" href="{{ route('guideline.show') }}">{{ __('guideline.view_button') }} →</a>
            </div>
            <div class="guideline-preview-roles stagger-children" aria-label="{{ __('guideline.choose_role') }}">
                @foreach(['oku_user' => 'profile', 'employer' => 'briefcase', 'jkm_officer' => 'government'] as $guideRole => $guideIcon)
                    <a href="{{ route('guideline.show', ['role' => $guideRole]) }}">
                        <span aria-hidden="true"><x-dashboard-icon :name="$guideIcon"/></span>
                        <strong>{{ __("guideline.role.{$guideRole}") }}</strong>
                        <small>{{ __("guideline.role.{$guideRole}_text") }}</small>
                    </a>
                @endforeach
            </div>
        </section>

        <section id="features" class="welcome-section" aria-labelledby="features-heading">
            <div class="section-title reveal">
                <p class="eyebrow">{{ __('ui.apa_yang_myokucare_sediakan.312d16c6') }}</p>
                <h2 id="features-heading">{{ __('ui.enam_teras_perkhidmatan_utama.94796059') }}</h2>
                <p>{{ __('ui.platform_menyeluruh_yang_direka_untuk_memenuhi_keperluan.54c29fbd') }}</p>
            </div>
            <div class="feature-cards stagger-children">
                <x-landing.feature-card icon="jobs" title="{{ __('ui.padanan_pekerjaan_inklusif.234bcf08') }}">
                    {{ __('ui.padanan_pintar_berdasarkan_kategori_oku_kemahiran_dan.1bb24525') }}
                </x-landing.feature-card>
                <x-landing.feature-card icon="id-card" title="{{ __('ui.pengurusan_data_berpusat.42447e79') }}">
                    {{ __('ui.semua_rekod_oku_dokumen_sokongan_dan_sejarah.2e55e6ea') }}
                </x-landing.feature-card>
                <x-landing.feature-card icon="welfare" title="{{ __('ui.permohonan_kebajikan_digital.3f1e6703') }}">
                    {{ __('ui.mohon_semak_dan_urus_bantuan_jkm_secara.fe79ce5a') }}
                </x-landing.feature-card>
                <x-landing.feature-card icon="employer" title="{{ __('ui.dashboard_majikan.7679c735') }}">
                    {{ __('ui.terbitkan_jawatan_kosong_urus_calon_oku_dan.c9156a80') }}
                </x-landing.feature-card>
                <x-landing.feature-card icon="employment-report" title="{{ __('ui.laporan_analitik.7064e357') }}">
                    {{ __('ui.statistik_semasa_carta_prestasi_dan_laporan_terperinci.c72e6a9c') }}
                </x-landing.feature-card>
                <x-landing.feature-card icon="settings" title="{{ __('ui.aksesibiliti_menyeluruh.ebc300df') }}">
                    {{ __('ui.antara_muka_mesra_pembaca_skrin_sokongan_kontras.79fe112f') }}
                </x-landing.feature-card>
            </div>
            <div class="feature-highlights reveal">
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ __('ui.berpandukan_keperluan_jkm.1338d683') }}</strong><small>{{ __('ui.prototaip_ini_dibangunkan_berdasarkan_keperluan_pengurusan_dan.cde71036') }}</small></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ __('ui.percuma_untuk_semua.81ac8e97') }}</strong><small>{{ __('ui.tiada_caj_pendaftaran_atau_langganan_semua_perkhidmatan.fe53ea52') }}</small></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ __('ui.data_selamat_terpelihara.e53dbde0') }}</strong><small>{{ __('ui.maklumat_peribadi_dan_dokumen_dilindungi_dengan_piawaian.9d299828') }}</small></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ __('ui.akses_mengikut_peranan.93d2648f') }}</strong><small>{{ __('ui.paparan_khusus_untuk_pengguna_oku_majikan_dan.47147fcb') }}</small></div>
                </div>
            </div>
        </section>

        <section id="audience" class="audience-section" aria-labelledby="audience-heading">
            <div class="section-title reveal">
                <p class="eyebrow">{{ __('ui.untuk_siapa_myokucare.e2701ed4') }}</p>
                <h2 id="audience-heading">{{ __('ui.platform_untuk_setiap_lapisan_komuniti.4f3b6146') }}</h2>
                <p>{{ __('ui.myokucare_direka_untuk_memenuhi_keperluan_pelbagai_pengguna.3a03f538') }}</p>
            </div>
            <div class="audience-grid stagger-children">
                <article class="audience-card card-lift">
                    <div class="audience-visual card-lift-icon" aria-hidden="true"><x-dashboard-icon name="profile"/></div>
                    <h3>{{ __('ui.individu_oku.db976eba') }}</h3>
                    <p>{{ __('ui.akses_profil_digital_peribadi_cari_pekerjaan_yang.6152655e') }}</p>
                </article>
                <article class="audience-card card-lift">
                    <div class="audience-visual card-lift-icon" aria-hidden="true"><x-dashboard-icon name="briefcase"/></div>
                    <h3>{{ __('ui.majikan_inklusif.042bfe62') }}</h3>
                    <p>{{ __('ui.daftar_organisasi_terbit_peluang_pekerjaan_cari_bakat.26868e76') }}</p>
                </article>
                <article class="audience-card card-lift">
                    <div class="audience-visual card-lift-icon" aria-hidden="true"><x-dashboard-icon name="settings"/></div>
                    <h3>{{ __('ui.pegawai_jkm.31c195cb') }}</h3>
                    <p>{{ __('ui.urus_rekod_oku_semak_dan_lulus_permohonan.f730d3af') }}</p>
                </article>
            </div>
        </section>

        <section id="accessibility" class="accessibility-section reveal" aria-labelledby="accessibility-heading">
            <div class="accessibility-copy">
                <p class="eyebrow">{{ __('ui.akses_untuk_semua.b86be9b2') }}</p>
                <h2 id="accessibility-heading">{{ __('ui.direka_supaya_lebih_mudah_dilihat_didengar_dan.afe0b722') }}</h2>
                <p>{{ __('ui.myokucare_direka_dengan_sasaran_wcag_2_2.57d7c651') }}</p>
            </div>
            <ul class="accessibility-list stagger-children">
                <li class="card-lift"><x-dashboard-icon name="profile" class="icon-bounce" /><span><strong>{{ __('ui.struktur_semantik.04480ada') }}</strong><small>{{ __('ui.label_heading_dan_susunan_kandungan_yang_mesra.46c10add') }}</small></span></li>
                <li class="card-lift"><x-dashboard-icon name="settings" class="icon-bounce" /><span><strong>{{ __('ui.paparan_boleh_dilaras.b66272d8') }}</strong><small>{{ __('ui.besarkan_teks_aktifkan_kontras_tinggi_atau_gunakan.cc352105') }}</small></span></li>
                <li class="card-lift"><x-dashboard-icon name="dashboard" class="icon-bounce" /><span><strong>{{ __('ui.responsif_dan_pwa.6a1d0d9c') }}</strong><small>{{ __('ui.sesuai_untuk_telefon_tablet_dan_boleh_dipasang.3a8b1a1e') }}</small></span></li>
            </ul>
        </section>




        <section id="faq" class="faq-section" aria-labelledby="faq-heading">
            <div class="section-title reveal">
                <p class="eyebrow">{{ __('ui.soalan_lazim.772ad0ce') }}</p>
                <h2 id="faq-heading">{{ __('ui.ada_soalan_kami_sedia_menjawab.4fe702df') }}</h2>
                <p>{{ __('ui.beberapa_soalan_yang_sering_ditanya_tentang_myokucare.b26687be') }}</p>
            </div>
            <div class="faq-list stagger-children">
                <details class="faq-item" open>
                    <summary><span>{{ __('ui.apa_itu_myokucare.d522c1f4') }}</span><span aria-hidden="true">+</span></summary>
                    <p>{{ __('ui.myokucare_ialah_prototaip_platform_digital_yang_menyatukan.6651e25c') }}</p>
                </details>
                <details class="faq-item">
                    <summary><span>{{ __('ui.siapa_yang_boleh_menggunakan_myokucare.aa6be742') }}</span><span aria-hidden="true">+</span></summary>
                    <p>{{ __('ui.pendaftaran_awam_tersedia_kepada_individu_oku_dan.4a55423c') }}</p>
                </details>
                <details class="faq-item">
                    <summary><span>{{ __('ui.adakah_myokucare_percuma.faf6d4f0') }}</span><span aria-hidden="true">+</span></summary>
                    <p>{{ __('ui.ya_semua_perkhidmatan_asas_myokucare_adalah_percuma.95b6d150') }}</p>
                </details>
            </div>
            <div class="faq-more reveal">
                <a href="#faq" class="btn btn-outline">{{ __('ui.lihat_soalan_lengkap.41232c94') }}</a>
            </div>
        </section>

        <section id="announcement" class="announcement-section reveal" aria-labelledby="announcement-heading">
            <div class="announcement-inner card-lift">
                <div class="announcement-badge" aria-hidden="true">TERKINI</div>
                <div class="announcement-copy">
                    <p class="eyebrow">{{ __('ui.pengumuman.c02bad08') }}</p>
                    <h2 id="announcement-heading">{{ __('ui.myokucare_kini_menyokong_pendaftaran_oku_secara_digital.5c1b8636') }}</h2>
                    <p>{{ __('ui.individu_oku_boleh_mendaftar_secara_dalam_talian.78ba5bb1') }}</p>
                    <a href="{{ route('register') }}" class="announcement-link">{{ __('ui.ketahui_lebih_lanjut.f81aaa2f') }}</a>
                </div>
            </div>
        </section>

        <section id="trust" class="trust-section" aria-labelledby="trust-heading">
            <div class="section-title reveal">
                <p class="eyebrow">{{ __('ui.konteks_piawaian.e05d4d61') }}</p>
                <h2 id="trust-heading">{{ __('ui.dibangunkan_untuk_menyokong_perkhidmatan_kebajikan.bb07678e') }}</h2>
                <p>{{ __('ui.myokucare_ialah_prototaip_yang_memerlukan_ujian_pengesahan.3300fded') }}</p>
            </div>
            <div class="trust-badges stagger-children">
                <div class="trust-badge card-lift">
                    <span class="trust-emblem card-lift-icon" aria-hidden="true">⚙</span>
                    <div><strong>{{ __('ui.konteks_perkhidmatan_jkm.ada9fe31') }}</strong><small>{{ __('ui.direka_berdasarkan_aliran_pengurusan_oku_pekerjaan_dan.7e1abc9e') }}</small></div>
                </div>
                <div class="trust-badge card-lift">
                    <span class="trust-emblem card-lift-icon" aria-hidden="true">🛡</span>
                    <div><strong>{{ __('ui.prototaip_digital.955cad86') }}</strong><small>{{ __('ui.sedia_untuk_penilaian_ujian_pengguna_dan_penambahbaikan.dfb01adf') }}</small></div>
                </div>
                <div class="trust-badge card-lift">
                    <span class="trust-emblem card-lift-icon" aria-hidden="true">✓</span>
                    <div><strong>{{ __('ui.sasaran_wcag_2_2_aa.f6c4efd7') }}</strong><small>{{ __('ui.direka_menuju_garis_panduan_aksesibiliti_kandungan_web.1f6af931') }}</small></div>
                </div>
            </div>
        </section>


        <x-landing.cta />
    </main>

    <footer class="main-footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <span class="footer-logo" aria-hidden="true"><img src="{{ asset('images/myokucare-logo.png') }}" alt="" loading="lazy"></span>
                <strong>MyOKUcare</strong>
                <p>{{ __('ui.prototaip_platform_digital_sokongan_oku_yang_menyatukan.2617a09b') }}</p>
            </div>
            <div class="footer-links footer-accordion">
                <button class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-section-services">
                    <strong>{{ __('ui.perkhidmatan.0b65e05f') }}</strong>
                    <span class="footer-chevron" aria-hidden="true">▼</span>
                </button>
                <div id="footer-section-services" class="footer-accordion-panel" role="region" aria-labelledby="footer-heading-services">
                    <a href="#features">{{ __('ui.ciri_utama.a1c5aa01') }}</a>
                    <a href="#how-it-works">{{ __('ui.cara_berfungsi.f61ac63e') }}</a>
                    <a href="#audience">{{ __('ui.untuk_siapa.b718501d') }}</a>
                    <a href="#accessibility">{{ __('ui.aksesibiliti.b9df4812') }}</a>
                </div>
            </div>
            <div class="footer-links footer-accordion">
                <button class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-section-resources">
                    <strong>{{ __('ui.sumber.ff648afc') }}</strong>
                    <span class="footer-chevron" aria-hidden="true">▼</span>
                </button>
                <div id="footer-section-resources" class="footer-accordion-panel" role="region" aria-labelledby="footer-heading-resources">
                    <a href="#faq">{{ __('ui.soalan_lazim.772ad0ce') }}</a>
                    <a href="#announcement">{{ __('ui.pengumuman.c02bad08') }}</a>
                    <a href="#trust">{{ __('ui.kredibiliti.b7bc8063') }}</a>
                </div>
            </div>
            <div class="footer-links footer-accordion">
                <button class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-section-contact">
                    <strong>{{ __('ui.hubungi.aff04813') }}</strong>
                    <span class="footer-chevron" aria-hidden="true">▼</span>
                </button>
                <div id="footer-section-contact" class="footer-accordion-panel" role="region" aria-labelledby="footer-heading-contact">
                    <span class="footer-contact">{{ __('ui.rujukan_perkhidmatan_jabatan_kebajikan_masyarakat_malaysia.09809cc9') }}</span>
                    <a class="footer-contact" href="tel:+601800883555" aria-label="{{ __('ui.hubungi_1800_88_3555.ea60664a') }}">{{ __('ui.talian_1800_88_3555.22e21180') }}</a>
                    <span class="footer-contact">{{ __('ui.saluran_khusus_myokucare_akan_diumumkan_selepas_kelulusan.389937bf') }}</span>
                    <a class="footer-contact" href="https://maps.google.com/?q=Jabatan+Kebajikan+Masyarakat+Malaysia+KPWKM" target="_blank" rel="noopener" aria-label="{{ __('ui.buka_lokasi_di_google_maps.3fdaa3f1') }}">{{ __('ui.pejabat_aras_12_blok_b_menara_kpwkm.f10b149a') }}</a>
                </div>
            </div>
            <div class="footer-social">
                <strong>{{ __('ui.ikuti_kami.dc27bdc1') }}</strong>
                <div class="footer-social-row">
                    <a href="https://www.facebook.com/jkmmalaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="{{ __('ui.facebook_jkm_malaysia.dd819052') }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                    <a href="https://www.instagram.com/jkm_malaysia/" target="_blank" rel="noopener" class="footer-social-btn" aria-label="{{ __('ui.instagram_jkm_malaysia.77f48218') }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                    <a href="https://www.tiktok.com/@jkm_malaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="{{ __('ui.tiktok_jkm_malaysia.44c2470e') }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></a>
                    <a href="https://www.youtube.com/@jkm_malaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="{{ __('ui.youtube_jkm_malaysia.8a9f722d') }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                    <a href="https://x.com/jkm_malaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="{{ __('ui.x_twitter_jkm_malaysia.fb44ca9c') }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <small>© {{ date('Y') }} MyOKUcare. Hak cipta terpelihara. Sistem Sokongan OKU — Jabatan Kebajikan Masyarakat Malaysia.</small>
            <small class="footer-version">v{{ config('app.version', '2.0') }}</small>
        </div>
    </footer>

    <button class="scroll-top" id="scrollTop" aria-label="{{ __('ui.ke_atas.36244ce1') }}" hidden>{{ __('ui.uarr.b9187882') }}</button>
</div>

<script>
const standaloneQuery=window.matchMedia('(display-mode: standalone)');
const syncStandaloneMode=()=>document.documentElement.classList.toggle('is-standalone',standaloneQuery.matches||window.navigator.standalone===true);
syncStandaloneMode();
standaloneQuery.addEventListener?.('change',syncStandaloneMode);

document.querySelectorAll('.faq-item summary').forEach(summary => {
    summary.addEventListener('click', e => {
        const details = summary.closest('details');
        if (!details) return;
        e.preventDefault();
        const isOpen = details.hasAttribute('open');
        document.querySelectorAll('.faq-item[open]').forEach(d => { d.removeAttribute('open'); });
        if (!isOpen) details.setAttribute('open', '');
    });
});
const scrollBtn = document.getElementById('scrollTop');
if (scrollBtn) {
    const footer = document.querySelector('.main-footer');
    window.addEventListener('scroll', () => {
        const cur = window.scrollY;
        const nearFooter = footer && cur + window.innerHeight > footer.offsetTop - 80;
        scrollBtn.hidden = cur < 400 || nearFooter;
        scrollBtn.classList.toggle('show', cur >= 400 && !nearFooter);
        scrollBtn.classList.toggle('hide-near-footer', nearFooter);
    }, { passive: true });
    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}
</script>
</body>
</html>
