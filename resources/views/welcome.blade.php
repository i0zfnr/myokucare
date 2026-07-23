<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF6565">
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

        <section class="welcome-section" aria-labelledby="features-heading">
            <div class="section-title">
                <p class="eyebrow">Apa yang MyOKUcare sediakan</p>
                <h2 id="features-heading">Satu platform untuk setiap langkah sokongan</h2>
                <p>Direka untuk komuniti OKU, ahli keluarga, majikan dan pegawai JKM dengan paparan khusus mengikut peranan.</p>
            </div>
            <div class="feature-cards">
                <x-landing.feature-card icon="id-card" title="Profil dan Rekod OKU">
                    Maklumat individu disimpan secara tersusun untuk memudahkan pengurusan dan tindakan susulan.
                </x-landing.feature-card>
                <x-landing.feature-card icon="briefcase" title="Pekerjaan Inklusif">
                    Temui peluang pekerjaan yang dipadankan dengan kategori dan keperluan individu.
                </x-landing.feature-card>
                <x-landing.feature-card icon="welfare" title="Sokongan Kebajikan">
                    Pantau permohonan, proses semakan dan bantuan kebajikan melalui satu aliran yang jelas.
                </x-landing.feature-card>
            </div>
        </section>

        <x-landing.cta />
    </main>
    <footer class="public-footer">© {{ date('Y') }} MyOKUcare. Sistem sokongan komuniti OKU.</footer>
</div>
</body>
</html>
