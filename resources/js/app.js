import DashboardLive from './modules/dashboard-live';
import Guideline from './modules/guideline';
import IdentityVerification from './modules/identity-verification';
import PushNotifications from './modules/push-notifications';
const t = (key) => window.MyOKUcareI18n?.[key] ?? key;

/* ── PWA ── */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
const standaloneMode = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
document.documentElement.classList.toggle('is-standalone', standaloneMode);
new PushNotifications({ translate: t, standalone: standaloneMode }).init().catch(() => {});
const guidelineVersion = document.querySelector('meta[name="guideline-version"]')?.content;
const guidelineCompletedByUser = document.querySelector('meta[name="guideline-completed"]')?.content === '1';
const guidelineAuthenticated = document.querySelector('meta[name="guideline-authenticated"]')?.content === '1';
const guidelineTrackUrl = document.querySelector('meta[name="guideline-track-url"]')?.content;
const guidelineStorageKey = guidelineVersion ? `myokucare-guideline-v${guidelineVersion}` : null;
if (guidelineStorageKey && guidelineCompletedByUser) localStorage.setItem(guidelineStorageKey, '1');
if (guidelineStorageKey && guidelineAuthenticated && !guidelineCompletedByUser
    && localStorage.getItem(guidelineStorageKey) === '1' && guidelineTrackUrl) {
    fetch(guidelineTrackUrl, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({ action: 'COMPLETED', device_type: standaloneMode ? 'PWA' : 'WEB' }),
    }).catch(() => {});
}
if (standaloneMode && guidelineStorageKey && window.location.pathname !== '/guideline'
    && localStorage.getItem(guidelineStorageKey) !== '1' && !guidelineCompletedByUser) {
    window.location.replace('/guideline?onboarding=1&source=pwa');
}
let pendingInstallPrompt;
const installDismissedKey = 'myokucare-install-dismissed-v1';
const installDismissalDuration = 14 * 24 * 60 * 60 * 1000;
const installPromptDismissed = () => {
    try {
        const dismissedAt = Number(localStorage.getItem(installDismissedKey) || 0);
        return dismissedAt > 0 && Date.now() - dismissedAt < installDismissalDuration;
    } catch {
        return false;
    }
};
const rememberInstallDismissal = () => {
    try {
        localStorage.setItem(installDismissedKey, String(Date.now()));
    } catch {
        // Storage can be unavailable in private browsing; hiding still works for this page.
    }
};
const installPanel = document.createElement('aside');
installPanel.className = 'pwa-install-panel';
installPanel.hidden = true;
installPanel.innerHTML = `
    <div class="pwa-install-icon" aria-hidden="true"><img src="/images/myokucare-logo.png" alt=""></div>
    <div><strong>${t('install_title')}</strong><span>${t('install_copy')}</span></div>
    <button class="pwa-install-button" type="button">${t('install')}</button>
    <button class="pwa-install-close" type="button" aria-label="${t('close_install')}">×</button>`;
document.body.appendChild(installPanel);
window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    pendingInstallPrompt = event;
    installPanel.hidden = standaloneMode || installPromptDismissed();
});
installPanel.querySelector('.pwa-install-button').addEventListener('click', async () => {
    if (!pendingInstallPrompt) return;
    pendingInstallPrompt.prompt();
    await pendingInstallPrompt.userChoice;
    pendingInstallPrompt = undefined;
    installPanel.hidden = true;
});
installPanel.querySelector('.pwa-install-close').addEventListener('click', () => {
    rememberInstallDismissal();
    installPanel.hidden = true;
});
window.addEventListener('appinstalled', () => {
    pendingInstallPrompt = undefined;
    rememberInstallDismissal();
    installPanel.hidden = true;
});

/* ── Online/Offline Notice ── */
const connectionNotice = document.createElement('div');
connectionNotice.className = 'connection-notice';
connectionNotice.setAttribute('role', 'status');
connectionNotice.hidden = true;
document.body.appendChild(connectionNotice);
const updateConnectionStatus = () => {
    connectionNotice.textContent = navigator.onLine
        ? t('online')
        : t('online_required');
    connectionNotice.classList.toggle('is-online', navigator.onLine);
    connectionNotice.hidden = false;
    if (navigator.onLine) window.setTimeout(() => { connectionNotice.hidden = true; }, 3500);
};
window.addEventListener('online', updateConnectionStatus);
window.addEventListener('offline', updateConnectionStatus);
if (!navigator.onLine) updateConnectionStatus();
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || navigator.onLine || form.method.toLowerCase() === 'get') return;
    event.preventDefault();
    updateConnectionStatus();
    connectionNotice.focus?.();
});

/* ── Live Dashboard ── */
const liveDashboard = document.querySelector('[data-live-dashboard]');
const dashboardRefresh = Number(document.documentElement.dataset.dashboardRefresh || 10) * 1000;
if (liveDashboard) new DashboardLive(liveDashboard, dashboardRefresh).init();

const identityVerification = document.querySelector('[data-identity-verification]');
if (identityVerification) new IdentityVerification(identityVerification).init();

const guideline = document.querySelector('[data-guideline]');
if (guideline) new Guideline(guideline).init();

/* ── Font Scale + Contrast ── */
const displayScales = [100, 112.5, 125, 137.5];
const preferencesVersion = document.documentElement.dataset.preferencesVersion || '';
const storedPreferencesVersion = localStorage.getItem('myokucare-preferences-version');
if (preferencesVersion && preferencesVersion !== storedPreferencesVersion) {
    localStorage.setItem('myokucare-font-scale', document.documentElement.dataset.defaultFontScale || '100');
    localStorage.setItem('myokucare-high-contrast', document.documentElement.dataset.defaultHighContrast || '0');
    localStorage.setItem('myokucare-preferences-version', preferencesVersion);
}
const savedScale = Number.parseFloat(localStorage.getItem('myokucare-font-scale') || document.documentElement.dataset.defaultFontScale || '100');
let displayScaleIndex = displayScales.indexOf(savedScale);
if (displayScaleIndex < 0) displayScaleIndex = 0;
const applyDisplayScale = () => {
    const scale = displayScales[displayScaleIndex];
    document.documentElement.style.setProperty('--user-font-scale', `${scale}%`);
    localStorage.setItem('myokucare-font-scale', String(scale));
};
document.querySelector('[data-font-action="increase"]')?.addEventListener('click', () => {
    displayScaleIndex = Math.min(displayScales.length - 1, displayScaleIndex + 1);
    applyDisplayScale();
});
document.querySelector('[data-font-action="decrease"]')?.addEventListener('click', () => {
    displayScaleIndex = Math.max(0, displayScaleIndex - 1);
    applyDisplayScale();
});
const contrastButton = document.querySelector('[data-contrast-toggle]');
const applyContrast = (enabled) => {
    document.documentElement.classList.toggle('high-contrast', enabled);
    contrastButton?.setAttribute('aria-pressed', String(enabled));
    localStorage.setItem('myokucare-high-contrast', enabled ? '1' : '0');
};
applyDisplayScale();
applyContrast(localStorage.getItem('myokucare-high-contrast') === '1');
contrastButton?.addEventListener('click', () => applyContrast(!document.documentElement.classList.contains('high-contrast')));

/* ── File Input ── */
document.querySelectorAll('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const status = document.getElementById(input.dataset.fileStatus);
        if (!status) return;
        const file = input.files?.[0];
        status.textContent = file
            ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`
            : t('no_file');
    });
});

/* ── Print ── */
document.querySelector('[data-print-report]')?.addEventListener('click', () => window.print());

/* ── Role Link Fields ── */
const managedRoleSelect = document.querySelector('[data-role-select]');
const syncRoleLinkFields = () => {
    if (!managedRoleSelect) return;
    document.querySelectorAll('[data-role-link]').forEach((field) => {
        const visible = field.dataset.roleLink.split(',').includes(managedRoleSelect.value);
        field.hidden = !visible;
        field.querySelector('select')?.toggleAttribute('required', visible);
    });
};
managedRoleSelect?.addEventListener('change', syncRoleLinkFields);
syncRoleLinkFields();

/* ── Password Toggle ── */
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-pressed', String(!showing));
        button.setAttribute('aria-label', showing ? t('show_password') : t('hide_password'));
        input.focus({ preventScroll: true });
    });
});

/* ── Login Submit ── */
document.querySelector('[data-login-form]')?.addEventListener('submit', (event) => {
    if (!event.currentTarget.checkValidity()) return;
    const button = event.currentTarget.querySelector('[data-login-submit]');
    const label = event.currentTarget.querySelector('[data-login-submit-label]');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    if (label) label.textContent = t('logging_in');
    const spinner = document.createElement('span');
    spinner.className = 'login-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    button.prepend(spinner);
});

/* ── Footer Accordion ── */
(() => {
    const isMobile = () => window.innerWidth <= 600;
    const initAccordions = () => {
        document.querySelectorAll('.footer-accordion-trigger').forEach((btn) => {
            btn.removeEventListener('click', btn._accordionHandler);
            btn._accordionHandler = () => {
                if (!isMobile()) return;
                const panel = document.getElementById(btn.getAttribute('aria-controls'));
                if (!panel) return;
                const expanded = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', String(!expanded));
                panel.classList.toggle('open', !expanded);
            };
            btn.addEventListener('click', btn._accordionHandler);
            const panel = document.getElementById(btn.getAttribute('aria-controls'));
            if (panel) {
                if (isMobile()) {
                    panel.classList.remove('open');
                    btn.setAttribute('aria-expanded', 'false');
                } else {
                    panel.classList.add('open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            }
        });
    };
    initAccordions();
    window.addEventListener('resize', initAccordions);
})();

/* ── Mobile Drawer ── */
(() => {
    const openBtn = document.querySelector('.mobile-menu-btn');
    const drawer = document.getElementById('mobileDrawer');
    const overlay = document.getElementById('mobileDrawerOverlay');
    const closeBtn = drawer?.querySelector('.mobile-drawer-close');
    if (!drawer || !overlay || !openBtn) return;
    const openDrawer = () => {
        drawer.classList.add('open');
        overlay.classList.add('open');
        document.body.classList.add('drawer-open');
        openBtn.setAttribute('aria-expanded', 'true');
        closeBtn?.focus();
    };
    const closeDrawer = () => {
        drawer.classList.remove('open');
        overlay.classList.remove('open');
        document.body.classList.remove('drawer-open');
        openBtn.setAttribute('aria-expanded', 'false');
        openBtn.focus();
    };
    openBtn.addEventListener('click', openDrawer);
    closeBtn?.addEventListener('click', closeDrawer);
    overlay.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && drawer.classList.contains('open')) closeDrawer();
    });
    drawer.querySelectorAll('.mobile-drawer-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (link.getAttribute('href')?.startsWith('#')) closeDrawer();
        });
    });
})();

/* ── Dashboard Sidebar Mobile Toggle ── */
/* NOTE: Primary open/close logic is in layout.blade.php via setSidebarOpen().
   This handler adds outside-click dismissal and ensures body.sidebar-open is always in sync. */
(() => {
    const menuBtn = document.getElementById('menuButton');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('backdrop');
    if (!menuBtn || !sidebar) return;

    const closeSidebar = () => {
        document.body.classList.remove('sidebar-open');
        sidebar.classList.remove('open');
        backdrop?.classList.remove('open');
        menuBtn.setAttribute('aria-expanded', 'false');
    };

    /* Outside-click dismissal on mobile */
    document.addEventListener('click', (e) => {
        if (window.innerWidth > 760) return;
        if (!document.body.classList.contains('sidebar-open')) return;
        if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
            closeSidebar();
        }
    });

    /* Backdrop click (belt-and-suspenders alongside layout.blade.php) */
    backdrop?.addEventListener('click', closeSidebar);
})();

/* ══════════════════════════════════════════════════
   ANIMATION SYSTEM
   ══════════════════════════════════════════════════ */

/* ── Scroll Reveal (IntersectionObserver) ── */
(() => {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('visible');
            revealObserver.unobserve(entry.target);
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    const staggerObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('stagger-visible');
            staggerObserver.unobserve(entry.target);
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal, .reveal-scale').forEach((el) => revealObserver.observe(el));
    document.querySelectorAll('.stagger-children > *').forEach((el) => staggerObserver.observe(el));
})();

/* ── Counter Animation ── */
(() => {
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const el = entry.target;
            const target = Number(el.getAttribute('data-target'));
            const duration = Number(el.getAttribute('data-duration') || 1500);
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.round(eased * target).toLocaleString();
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
            counterObserver.unobserve(el);
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('[data-counter]').forEach((el) => counterObserver.observe(el));
})();

/* ── Button Ripple ── */
(() => {
    document.querySelectorAll('.btn-ripple').forEach((btn) => {
        btn.addEventListener('click', function (e) {
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            const ripple = document.createElement('span');
            ripple.className = 'ripple-el';
            ripple.style.width = ripple.style.height = `${size}px`;
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;
            this.appendChild(ripple);
            ripple.addEventListener('animationend', () => ripple.remove());
        });
    });
})();

/* ── Toast Notification System ── */
(() => {
    const container = document.createElement('div');
    container.className = 'toast-container';
    container.setAttribute('aria-live', 'polite');
    document.body.appendChild(container);

    const icons = { success: '✓', error: '✗', warning: '⚠', info: 'ℹ' };

    window.toast = ({ type = 'info', title, message, duration = 4000 } = {}) => {
        const el = document.createElement('div');
        el.className = `toast toast-${type}`;
        el.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <div class="toast-body"><strong>${title}</strong><span>${message}</span></div>
            <button class="toast-close" type="button" aria-label="Tutup">×</button>
            <div class="toast-progress" style="animation-duration:${duration}ms"></div>`;
        container.appendChild(el);
        requestAnimationFrame(() => el.classList.add('show'));

        const dismiss = () => {
            el.classList.remove('show');
            el.classList.add('toast-exit');
            el.addEventListener('transitionend', () => el.remove(), { once: true });
        };

        el.querySelector('.toast-close').addEventListener('click', dismiss);
        const timer = setTimeout(dismiss, duration);
        el.addEventListener('mouseenter', () => clearTimeout(timer));
    };
})();

/* ── Modal System ── */
(() => {
    window.openModal = (html) => {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `<div class="modal-box">${html}</div>`;
        document.body.appendChild(overlay);
        requestAnimationFrame(() => overlay.classList.add('show'));
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeModal(overlay);
        });
        document.addEventListener('keydown', modalKeydown = (e) => {
            if (e.key === 'Escape') closeModal(overlay);
        });
        overlay.querySelector('.modal-close')?.addEventListener('click', () => closeModal(overlay));
        return overlay;
    };
    window.closeModal = (overlay) => {
        overlay.classList.remove('show');
        overlay.addEventListener('transitionend', () => overlay.remove(), { once: true });
        document.removeEventListener('keydown', modalKeydown);
    };
})();

/* ── Navbar Scroll Effect ── */
(() => {
    const nav = document.querySelector('.public-nav');
    if (!nav) return;
    const threshold = 80;
    const updateNav = () => nav.classList.toggle('nav-scrolled', window.scrollY > threshold);
    updateNav();
    window.addEventListener('scroll', updateNav, { passive: true });
})();

/* ── Page Transition ── */
(() => {
    document.documentElement.classList.add('page-enter');
    requestAnimationFrame(() => document.documentElement.classList.remove('page-enter'));
    document.querySelectorAll('[data-page-link]').forEach((link) => {
        link.addEventListener('click', (e) => {
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('http')) return;
            e.preventDefault();
            document.documentElement.classList.add('page-leave-active');
            setTimeout(() => { window.location = href; }, 250);
        });
    });
})();
