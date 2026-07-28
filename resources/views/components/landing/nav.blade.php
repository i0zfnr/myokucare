<header class="public-nav public-nav-scroll" role="banner">
    <div class="public-left-group">
        <button class="mobile-menu-btn" type="button" aria-label="{{ __('ui.buka_menu_navigasi.db4b2021') }}" aria-expanded="false" aria-controls="mobileDrawer">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <a class="public-brand" href="{{ route('welcome') }}" aria-label="{{ __('ui.myokucare_laman_utama.646801e2') }}">
            <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
            <span><strong>MyOKUcare</strong><small>{{ __('ui.platform_digital_sokongan_oku.7eca08df') }}</small></span>
        </a>
    </div>
    <nav class="public-section-links" aria-label="{{ __('ui.bahagian_halaman.467c041e') }}">
        <a href="#features">{{ __('ui.perkhidmatan.0b65e05f') }}</a>
        <a href="#how-it-works">{{ __('ui.cara_berfungsi.f61ac63e') }}</a>
        <a href="{{ route('guideline.show') }}">{{ __('guideline.nav') }}</a>
        <a href="#audience">{{ __('ui.pengguna.c720f761') }}</a>
        <a href="#faq">{{ __('ui.soalan.be176bda') }}</a>
    </nav>
    <div class="public-right-group">
        <div class="public-accessibility" role="toolbar" aria-label="{{ __('ui.kawalan_paparan.9b238eb8') }}">
            <button type="button" data-font-action="decrease" aria-label="{{ __('ui.kecilkan_saiz_teks.0f2ef075') }}">A−</button>
            <button type="button" data-font-action="increase" aria-label="{{ __('ui.besarkan_saiz_teks.27358982') }}">A+</button>
            <button type="button" data-contrast-toggle aria-label="{{ __('ui.tukar_mod_kontras_tinggi.136429a0') }}" aria-pressed="false">◐</button>
        </div>
        <nav class="public-actions" aria-label="{{ __('ui.akaun_pengguna.64ea096f') }}">
            <a class="btn login-link" href="{{ route('login') }}">{{ __('ui.log_masuk.65586411') }}</a>
            <a class="btn btn-primary" href="{{ route('register') }}">{{ __('ui.daftar_akaun.97f415a6') }}</a>
        </nav>
    </div>
</header>
