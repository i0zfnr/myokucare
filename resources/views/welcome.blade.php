<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="MyOKUcare menghubungkan komuniti OKU dengan pekerjaan, bantuan kebajikan dan sokongan JKM.">
    <meta property="og:title" content="MyOKUcare — Sokongan Inklusif untuk Semua">
    <meta property="og:description" content="Sistem sokongan komuniti OKU yang menghubungkan pekerjaan, kebajikan dan perkhidmatan JKM dalam satu platform.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="MyOKUcare — Sokongan Inklusif untuk Semua">
    <meta name="twitter:description" content="Sistem sokongan komuniti OKU untuk pekerjaan, kebajikan dan perkhidmatan JKM.">
    @include('partials.pwa-head')
    <title>MyOKUcare — Sokongan Inklusif untuk Semua</title>
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#main-content">Langkau ke kandungan utama</a>
<div class="welcome">
    <x-landing.nav />
    <main id="main-content" tabindex="-1">
        <x-landing.hero />

        <section id="features" class="welcome-section" aria-labelledby="features-heading">
            <div class="section-title">
                <p class="eyebrow">Apa yang MyOKUcare sediakan</p>
                <h2 id="features-heading">Satu platform untuk setiap langkah sokongan</h2>
                <p>Direka untuk komuniti OKU, ahli keluarga, majikan dan pegawai JKM dengan paparan khusus mengikut peranan.</p>
            </div>
            <div class="feature-cards">
                <x-landing.feature-card icon="id-card" title="Pengguna OKU">
                    Lengkapkan profil kerjaya, muat naik Kad OKU dan temui peluang berdasarkan kategori serta kemahiran anda.
                </x-landing.feature-card>
                <x-landing.feature-card icon="briefcase" title="Majikan Inklusif">
                    Daftar organisasi, terbitkan jawatan kosong dan urus calon dalam satu paparan yang tersusun.
                </x-landing.feature-card>
                <x-landing.feature-card icon="welfare" title="Pegawai JKM">
                    Urus rekod OKU, semak permohonan kebajikan dan pantau statistik semasa melalui panel khusus.
                </x-landing.feature-card>
            </div>
        </section>

        <section id="accessibility" class="accessibility-section" aria-labelledby="accessibility-heading">
            <div class="accessibility-copy">
                <p class="eyebrow">Akses untuk semua</p>
                <h2 id="accessibility-heading">Direka supaya lebih mudah dilihat dan digunakan</h2>
                <p>Antara muka MyOKUcare menyokong penggunaan papan kekunci, pembaca skrin, pelarasan saiz teks dan paparan kontras tinggi.</p>
            </div>
            <ul class="accessibility-list">
                <li><x-dashboard-icon name="profile" /><span><strong>Struktur semantik</strong><small>Label dan susunan kandungan yang mesra pembaca skrin.</small></span></li>
                <li><x-dashboard-icon name="settings" /><span><strong>Paparan boleh dilaras</strong><small>Besarkan teks atau aktifkan kontras tinggi pada bila-bila masa.</small></span></li>
                <li><x-dashboard-icon name="dashboard" /><span><strong>Responsif dan PWA</strong><small>Sesuai untuk telefon, tablet dan pemasangan pada skrin utama.</small></span></li>
            </ul>
        </section>

        <x-landing.cta />
    </main>
    <footer class="public-footer">© {{ date('Y') }} MyOKUcare. Sistem sokongan komuniti OKU.</footer>
</div>
</body>
</html>
