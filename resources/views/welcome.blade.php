<!doctype html>
<html lang="ms" data-default-font-scale="100" data-default-high-contrast="0" data-preferences-version="0" data-dashboard-refresh="10">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MyOKUcare — Platform digital JKM untuk pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan kebajikan dalam satu sistem selamat dan mudah dicapai.">
    <meta property="og:title" content="MyOKUcare — Platform Digital Sokongan OKU">
    <meta property="og:description" content="Diselia oleh JKM, MyOKUcare menyatukan data OKU, pekerjaan, dan kebajikan dalam satu platform inklusif.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="MyOKUcare — Platform Digital Sokongan OKU">
    <meta name="twitter:description" content="Diselia oleh JKM, MyOKUcare menyatukan data OKU, pekerjaan, dan kebajikan dalam satu platform inklusif.">
    @include('partials.pwa-head')
    <title>MyOKUcare — Platform Digital Sokongan OKU</title>
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/css/animate.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main-content">Langkau ke kandungan utama</a>
<div class="welcome">
    <x-landing.nav />
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
    <aside class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Menu navigasi">
        <div class="mobile-drawer-head">
            <strong>Menu</strong>
            <button class="mobile-drawer-close" type="button" aria-label="Tutup menu">✕</button>
        </div>
        <nav class="mobile-drawer-body" aria-label="Navigasi utama">
            <a class="mobile-drawer-link" href="#features">Perkhidmatan</a>
            <a class="mobile-drawer-link" href="#how-it-works">Cara Berfungsi</a>
            <a class="mobile-drawer-link" href="#audience">Pengguna</a>
            <a class="mobile-drawer-link" href="#faq">Soalan</a>
            <a class="mobile-drawer-link" href="{{ route('login') }}">Log Masuk</a>
            <a class="mobile-drawer-link" href="{{ route('register') }}">Daftar Akaun</a>
        </nav>
    </aside>
    <main id="main-content" tabindex="-1">
        <x-landing.hero />


        <section id="how-it-works" class="steps-section" aria-labelledby="steps-heading">
            <div class="section-title reveal">
                <p class="eyebrow">Cara MyOKUcare Berfungsi</p>
                <h2 id="steps-heading">Tiga langkah mudah untuk bermula</h2>
                <p>Daripada pendaftaran hingga akses penuh perkhidmatan — semuanya secara digital.</p>
            </div>
            <div class="steps-track stagger-children">
                <article class="step-card card-lift card-lift-border">
                    <span class="step-number" aria-hidden="true">1</span>
                    <div class="step-icon card-lift-icon" aria-hidden="true"><x-dashboard-icon name="add-record"/></div>
                    <h3>Daftar Akaun</h3>
                    <p>Buat akaun percuma mengikut peranan anda — OKU, majikan atau pegawai JKM. Proses cepat dan mudah.</p>
                </article>
                <article class="step-card card-lift card-lift-border">
                    <span class="step-number" aria-hidden="true">2</span>
                    <div class="step-icon card-lift-icon" aria-hidden="true"><x-dashboard-icon name="profile"/></div>
                    <h3>Lengkapkan Profil</h3>
                    <p>Isi maklumat peribadi, muat naik dokumen sokongan dan kemas kini kemahiran serta kategori OKU anda.</p>
                </article>
                <article class="step-card card-lift card-lift-border">
                    <span class="step-number" aria-hidden="true">3</span>
                    <div class="step-icon card-lift-icon" aria-hidden="true"><x-dashboard-icon name="dashboard"/></div>
                    <h3>Akses Perkhidmatan</h3>
                    <p>Teroka peluang pekerjaan, mohon bantuan kebajikan, urus rekod dan pantau statistik dalam satu dashboard.</p>
                </article>
            </div>
        </section>

        <section id="features" class="welcome-section" aria-labelledby="features-heading">
            <div class="section-title reveal">
                <p class="eyebrow">Apa yang MyOKUcare sediakan</p>
                <h2 id="features-heading">Enam teras perkhidmatan utama</h2>
                <p>Platform menyeluruh yang direka untuk memenuhi keperluan setiap pengguna dalam ekosistem sokongan OKU.</p>
            </div>
            <div class="feature-cards stagger-children">
                <x-landing.feature-card icon="jobs" title="Padanan Pekerjaan Inklusif">
                    Padanan pintar berdasarkan kategori OKU, kemahiran, dan lokasi untuk peluang pekerjaan yang lebih sesuai dan saksama.
                </x-landing.feature-card>
                <x-landing.feature-card icon="id-card" title="Pengurusan Data Berpusat">
                    Semua rekod OKU, dokumen sokongan, dan sejarah permohonan dalam satu sistem yang selamat dan teratur.
                </x-landing.feature-card>
                <x-landing.feature-card icon="welfare" title="Permohonan Kebajikan Digital">
                    Mohon, semak dan urus bantuan JKM secara dalam talian dengan proses yang telus dan masa nyata.
                </x-landing.feature-card>
                <x-landing.feature-card icon="employer" title="Dashboard Majikan">
                    Terbitkan jawatan kosong, urus calon OKU, dan pantau kepelbagaian tenaga kerja dalam organisasi anda.
                </x-landing.feature-card>
                <x-landing.feature-card icon="employment-report" title="Laporan & Analitik">
                    Statistik masa nyata, carta prestasi, dan laporan terperinci untuk pengurusan program OKU yang lebih baik.
                </x-landing.feature-card>
                <x-landing.feature-card icon="settings" title="Aksesibiliti Menyeluruh">
                    Antara muka mesra pembaca skrin, sokongan kontras tinggi, navigasi papan kekunci, dan PWA untuk capaian luar talian.
                </x-landing.feature-card>
            </div>
            <div class="feature-highlights reveal">
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>Diselia oleh JKM Malaysia</strong><small>Platform rasmi di bawah pengurusan JKM untuk kesahihan dan kebolehpercayaan data.</small></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>Percuma untuk Semua</strong><small>Tiada caj pendaftaran atau langganan. Semua perkhidmatan asas boleh diakses secara percuma.</small></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>Data Selamat & Terpelihara</strong><small>Maklumat peribadi dan dokumen dilindungi dengan piawaian keselamatan yang ketat.</small></div>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon" aria-hidden="true">✓</span>
                    <div><strong>Pelbagai Peranan</strong><small>Paparan khusus untuk OKU, majikan, keluarga dan pegawai JKM — semua dalam satu platform.</small></div>
                </div>
            </div>
        </section>

        <section id="audience" class="audience-section" aria-labelledby="audience-heading">
            <div class="section-title reveal">
                <p class="eyebrow">Untuk Siapa MyOKUcare</p>
                <h2 id="audience-heading">Platform untuk setiap lapisan komuniti</h2>
                <p>MyOKUcare direka untuk memenuhi keperluan pelbagai pengguna dalam ekosistem sokongan OKU negara.</p>
            </div>
            <div class="audience-grid stagger-children">
                <article class="audience-card card-lift">
                    <div class="audience-visual card-lift-icon" aria-hidden="true"><x-dashboard-icon name="profile"/></div>
                    <h3>Individu OKU</h3>
                    <p>Akses profil digital peribadi, cari pekerjaan yang sesuai dengan kemahiran, dan mohon bantuan kebajikan JKM secara dalam talian.</p>
                </article>
                <article class="audience-card card-lift">
                    <div class="audience-visual card-lift-icon" aria-hidden="true"><x-dashboard-icon name="briefcase"/></div>
                    <h3>Majikan Inklusif</h3>
                    <p>Daftar organisasi, terbit peluang pekerjaan, cari bakat OKU yang pelbagai dan bina tenaga kerja inklusif.</p>
                </article>
                <article class="audience-card card-lift">
                    <div class="audience-visual card-lift-icon" aria-hidden="true"><x-dashboard-icon name="settings"/></div>
                    <h3>Pegawai JKM</h3>
                    <p>Urus rekod OKU, semak dan lulus permohonan kebajikan, pantau statistik dan hasilkan laporan program.</p>
                </article>
            </div>
        </section>

        <section id="accessibility" class="accessibility-section reveal" aria-labelledby="accessibility-heading">
            <div class="accessibility-copy">
                <p class="eyebrow">Akses untuk semua</p>
                <h2 id="accessibility-heading">Direka supaya lebih mudah dilihat, didengar dan digunakan</h2>
                <p>MyOKUcare mematuhi piawaian aksesibiliti WCAG 2.1. Antara muka menyokong pembaca skrin, navigasi papan kekunci, pelarasan saiz teks dan paparan kontras tinggi — kerana akses digital adalah hak semua.</p>
            </div>
            <ul class="accessibility-list stagger-children">
                <li class="card-lift"><x-dashboard-icon name="profile" class="icon-bounce" /><span><strong>Struktur semantik</strong><small>Label, heading dan susunan kandungan yang mesra pembaca skrin serta navigasi logik.</small></span></li>
                <li class="card-lift"><x-dashboard-icon name="settings" class="icon-bounce" /><span><strong>Paparan boleh dilaras</strong><small>Besarkan teks, aktifkan kontras tinggi, atau gunakan mod gelap pada bila-bila masa.</small></span></li>
                <li class="card-lift"><x-dashboard-icon name="dashboard" class="icon-bounce" /><span><strong>Responsif dan PWA</strong><small>Sesuai untuk telefon, tablet, dan boleh dipasang pada skrin utama untuk capaian luar talian.</small></span></li>
            </ul>
        </section>




        <section id="faq" class="faq-section" aria-labelledby="faq-heading">
            <div class="section-title reveal">
                <p class="eyebrow">Soalan Lazim</p>
                <h2 id="faq-heading">Ada soalan? Kami sedia menjawab</h2>
                <p>Beberapa soalan yang sering ditanya tentang MyOKUcare dan cara ia berfungsi.</p>
            </div>
            <div class="faq-list stagger-children">
                <details class="faq-item" open>
                    <summary><span>Apa itu MyOKUcare?</span><span aria-hidden="true">+</span></summary>
                    <p>MyOKUcare adalah platform digital di bawah seliaan JKM Malaysia yang menyatukan pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan bantuan kebajikan dalam satu sistem yang selamat dan mudah dicapai.</p>
                </details>
                <details class="faq-item">
                    <summary><span>Siapa yang boleh menggunakan MyOKUcare?</span><span aria-hidden="true">+</span></summary>
                    <p>MyOKUcare dibuka kepada individu OKU, ahli keluarga atau penjaga, majikan yang mencari bakat inklusif, dan pegawai JKM yang menguruskan program sokongan OKU.</p>
                </details>
                <details class="faq-item">
                    <summary><span>Adakah MyOKUcare percuma?</span><span aria-hidden="true">+</span></summary>
                    <p>Ya. Semua perkhidmatan asas MyOKUcare adalah percuma untuk semua pengguna. Tiada caj pendaftaran, langganan atau yuran tersembunyi.</p>
                </details>
            </div>
            <div class="faq-more reveal">
                <a href="#faq" class="btn btn-outline">Lihat Soalan Lengkap →</a>
            </div>
        </section>

        <section id="announcement" class="announcement-section reveal" aria-labelledby="announcement-heading">
            <div class="announcement-inner card-lift">
                <div class="announcement-badge" aria-hidden="true">TERKINI</div>
                <div class="announcement-copy">
                    <p class="eyebrow">Pengumuman</p>
                    <h2 id="announcement-heading">MyOKUcare kini menyokong pendaftaran OKU secara digital</h2>
                    <p>Individu OKu kini boleh mendaftar dan mengemukakan dokumen secara dalam talian tanpa perlu hadir ke pejabat JKM. Semua proses pengesahan boleh dilakukan melalui platform.</p>
                    <a href="{{ route('register') }}" class="announcement-link">Ketahui Lebih Lanjut →</a>
                </div>
            </div>
        </section>

        <section id="trust" class="trust-section" aria-labelledby="trust-heading">
            <div class="section-title reveal">
                <p class="eyebrow">Kredibiliti & Amanah</p>
                <h2 id="trust-heading">Diselia dan diuruskan oleh agensi kerajaan</h2>
                <p>MyOKUcare beroperasi di bawah seliaan rasmi Jabatan Kebajikan Masyarakat Malaysia demi memastikan integriti dan kebolehpercayaan perkhidmatan.</p>
            </div>
            <div class="trust-badges stagger-children">
                <div class="trust-badge card-lift">
                    <span class="trust-emblem card-lift-icon" aria-hidden="true">⚙</span>
                    <div><strong>Jabatan Kebajikan Masyarakat</strong><small>Kementerian Pembangunan Wanita, Keluarga dan Masyarakat</small></div>
                </div>
                <div class="trust-badge card-lift">
                    <span class="trust-emblem card-lift-icon" aria-hidden="true">🛡</span>
                    <div><strong>Platform Digital Kerajaan</strong><small>Mematuhi piawaian keselamatan dan aksesibiliti sektor awam</small></div>
                </div>
                <div class="trust-badge card-lift">
                    <span class="trust-emblem card-lift-icon" aria-hidden="true">✓</span>
                    <div><strong>Piawaian WCAG 2.1</strong><small>Mematuhi garis panduan aksesibiliti kandungan web antarabangsa</small></div>
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
                <p>Platform digital sokongan OKU di bawah seliaan JKM Malaysia. Menyatukan data, pekerjaan dan kebajikan dalam satu sistem inklusif.</p>
            </div>
            <div class="footer-links footer-accordion">
                <button class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-section-services">
                    <strong>Perkhidmatan</strong>
                    <span class="footer-chevron" aria-hidden="true">▼</span>
                </button>
                <div id="footer-section-services" class="footer-accordion-panel" role="region" aria-labelledby="footer-heading-services">
                    <a href="#features">Ciri Utama</a>
                    <a href="#how-it-works">Cara Berfungsi</a>
                    <a href="#audience">Untuk Siapa</a>
                    <a href="#accessibility">Aksesibiliti</a>
                </div>
            </div>
            <div class="footer-links footer-accordion">
                <button class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-section-resources">
                    <strong>Sumber</strong>
                    <span class="footer-chevron" aria-hidden="true">▼</span>
                </button>
                <div id="footer-section-resources" class="footer-accordion-panel" role="region" aria-labelledby="footer-heading-resources">
                    <a href="#faq">Soalan Lazim</a>
                    <a href="#announcement">Pengumuman</a>
                    <a href="#trust">Kredibiliti</a>
                </div>
            </div>
            <div class="footer-links footer-accordion">
                <button class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-section-contact">
                    <strong>Hubungi</strong>
                    <span class="footer-chevron" aria-hidden="true">▼</span>
                </button>
                <div id="footer-section-contact" class="footer-accordion-panel" role="region" aria-labelledby="footer-heading-contact">
                    <span class="footer-contact">Jabatan Kebajikan Masyarakat Malaysia</span>
                    <a class="footer-contact" href="tel:+601800883555" aria-label="Hubungi 1800-88-3555">Talian: 1800-88-3555</a>
                    <a class="footer-contact" href="mailto:myokucare@jkm.gov.my" aria-label="E-mel myokucare@jkm.gov.my">E-mel: myokucare@jkm.gov.my</a>
                    <a class="footer-contact" href="https://maps.google.com/?q=Jabatan+Kebajikan+Masyarakat+Malaysia+KPWKM" target="_blank" rel="noopener" aria-label="Buka lokasi di Google Maps">Pejabat: Aras 12, Blok B, Menara KPWKM</a>
                </div>
            </div>
            <div class="footer-social">
                <strong>Ikuti Kami</strong>
                <div class="footer-social-row">
                    <a href="https://www.facebook.com/jkmmalaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="Facebook JKM Malaysia"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                    <a href="https://www.instagram.com/jkm_malaysia/" target="_blank" rel="noopener" class="footer-social-btn" aria-label="Instagram JKM Malaysia"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></a>
                    <a href="https://www.tiktok.com/@jkm_malaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="TikTok JKM Malaysia"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></a>
                    <a href="https://www.youtube.com/@jkm_malaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="YouTube JKM Malaysia"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                    <a href="https://x.com/jkm_malaysia" target="_blank" rel="noopener" class="footer-social-btn" aria-label="X (Twitter) JKM Malaysia"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <small>© {{ date('Y') }} MyOKUcare. Hak cipta terpelihara. Sistem Sokongan OKU — Jabatan Kebajikan Masyarakat Malaysia.</small>
            <small class="footer-version">v{{ config('app.version', '2.0') }}</small>
        </div>
    </footer>

    <button class="scroll-top" id="scrollTop" aria-label="Ke atas" hidden>&uarr;</button>
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
        const nearFooter = footer && cur + window.innerHeight >= footer.offsetTop - 80;
        scrollBtn.hidden = cur < 400 || nearFooter;
        scrollBtn.classList.toggle('show', cur >= 400 && !nearFooter);
        scrollBtn.classList.toggle('hide-near-footer', nearFooter);
    }, { passive: true });
    scrollBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}
</script>
</body>
</html>