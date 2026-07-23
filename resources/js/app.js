import DashboardLive from './modules/dashboard-live';

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // The application remains usable when service-worker registration is unavailable.
        });
    });
}

let pendingInstallPrompt;
const installPanel = document.createElement('aside');
installPanel.className = 'pwa-install-panel';
installPanel.hidden = true;
installPanel.innerHTML = `
    <div class="pwa-install-icon" aria-hidden="true"><img src="/images/myokucare-logo.png" alt=""></div>
    <div><strong>Pasang MyOKUcare</strong><span>Akses lebih pantas dari skrin utama telefon anda.</span></div>
    <button class="pwa-install-button" type="button">Pasang</button>
    <button class="pwa-install-close" type="button" aria-label="Tutup cadangan pemasangan">×</button>
`;
document.body.appendChild(installPanel);

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    pendingInstallPrompt = event;
    installPanel.hidden = false;
});

installPanel.querySelector('.pwa-install-button').addEventListener('click', async () => {
    if (!pendingInstallPrompt) return;
    pendingInstallPrompt.prompt();
    await pendingInstallPrompt.userChoice;
    pendingInstallPrompt = undefined;
    installPanel.hidden = true;
});

installPanel.querySelector('.pwa-install-close').addEventListener('click', () => {
    installPanel.hidden = true;
});

window.addEventListener('appinstalled', () => {
    pendingInstallPrompt = undefined;
    installPanel.hidden = true;
});

const connectionNotice = document.createElement('div');
connectionNotice.className = 'connection-notice';
connectionNotice.setAttribute('role', 'status');
connectionNotice.hidden = true;
document.body.appendChild(connectionNotice);

const updateConnectionStatus = () => {
    connectionNotice.textContent = navigator.onLine
        ? 'Sambungan internet dipulihkan.'
        : 'Anda sedang berada di luar talian.';
    connectionNotice.classList.toggle('is-online', navigator.onLine);
    connectionNotice.hidden = false;
    if (navigator.onLine) window.setTimeout(() => { connectionNotice.hidden = true; }, 3500);
};

window.addEventListener('online', updateConnectionStatus);
window.addEventListener('offline', updateConnectionStatus);

const liveDashboard = document.querySelector('[data-live-dashboard]');
const dashboardRefresh = Number(document.documentElement.dataset.dashboardRefresh || 10) * 1000;
if (liveDashboard) new DashboardLive(liveDashboard, dashboardRefresh).init();

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

document.querySelectorAll('[data-file-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const status = document.getElementById(input.dataset.fileStatus);
        if (!status) return;
        const file = input.files?.[0];
        status.textContent = file
            ? `${file.name} · ${(file.size / 1024 / 1024).toFixed(2)} MB`
            : 'Tiada fail dipilih.';
    });
});

document.querySelector('[data-print-report]')?.addEventListener('click', () => window.print());

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

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        if (!input) return;
        const showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        button.setAttribute('aria-pressed', String(!showing));
        button.setAttribute('aria-label', showing ? 'Tunjukkan kata laluan' : 'Sembunyikan kata laluan');
        input.focus({ preventScroll: true });
    });
});

document.querySelector('[data-login-form]')?.addEventListener('submit', (event) => {
    if (!event.currentTarget.checkValidity()) return;
    const button = event.currentTarget.querySelector('[data-login-submit]');
    const label = event.currentTarget.querySelector('[data-login-submit-label]');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    if (label) label.textContent = 'Sedang log masuk…';
    const spinner = document.createElement('span');
    spinner.className = 'login-spinner';
    spinner.setAttribute('aria-hidden', 'true');
    button.prepend(spinner);
});
