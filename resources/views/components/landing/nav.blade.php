<header class="public-nav" role="banner">
    <a class="public-brand" href="{{ route('welcome') }}" aria-label="MyOKUcare, laman utama">
        <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
        <span><strong>MyOKUcare</strong><small>Sistem Sokongan OKU</small></span>
    </a>
    <nav class="public-section-links" aria-label="Bahagian halaman">
        <a href="#features">Perkhidmatan</a>
        <a href="#accessibility">Aksesibiliti</a>
    </nav>
    <div class="public-accessibility" role="toolbar" aria-label="Kawalan paparan">
        <button type="button" data-font-action="decrease" aria-label="Kecilkan saiz teks">A−</button>
        <button type="button" data-font-action="increase" aria-label="Besarkan saiz teks">A+</button>
        <button type="button" data-contrast-toggle aria-label="Tukar mod kontras tinggi" aria-pressed="false">◐</button>
    </div>
    <nav class="public-actions" aria-label="Akaun pengguna">
        <a class="btn login-link" href="{{ route('login') }}">Log Masuk</a>
        <a class="btn btn-primary" href="{{ route('register') }}">Daftar Akaun</a>
    </nav>
</header>
