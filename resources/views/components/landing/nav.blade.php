<header class="public-nav public-nav-scroll" role="banner">
    <div class="public-left-group">
        <button class="mobile-menu-btn" type="button" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobileDrawer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <a class="public-brand" href="{{ route('welcome') }}" aria-label="MyOKUcare, laman utama">
            <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <span><strong>MyOKUcare</strong><small>Platform Digital Sokongan OKU</small></span>
        </a>
    </div>
    <nav class="public-section-links" aria-label="Bahagian halaman">
        <a href="#features">Perkhidmatan</a>
        <a href="#how-it-works">Cara Berfungsi</a>
        <a href="#audience">Pengguna</a>
        <a href="#faq">Soalan</a>
    </nav>
    <div class="public-right-group">
        <div class="public-accessibility" role="toolbar" aria-label="Kawalan paparan">
            <button type="button" data-font-action="decrease" aria-label="Kecilkan saiz teks">A−</button>
            <button type="button" data-font-action="increase" aria-label="Besarkan saiz teks">A+</button>
            <button type="button" data-contrast-toggle aria-label="Tukar mod kontras tinggi" aria-pressed="false">◐</button>
        </div>
        <nav class="public-actions" aria-label="Akaun pengguna">
            <a class="btn login-link" href="{{ route('login') }}">Log Masuk</a>
            <a class="btn btn-primary" href="{{ route('register') }}">Daftar Akaun</a>
        </nav>
    </div>
</header>