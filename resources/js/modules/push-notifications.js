import { initializeApp } from 'firebase/app';
import { deleteToken, getMessaging, getToken, isSupported, onMessage } from 'firebase/messaging';

export default class PushNotifications {
    constructor({ translate, standalone }) {
        this.t = translate;
        this.standalone = standalone;
        this.messaging = null;
        this.token = null;
        this.config = null;
        this.panel = null;
    }

    async init() {
        if (!this.standalone || document.querySelector('meta[name="push-authenticated"]')?.content !== '1') return;
        if (!('Notification' in window) || !('serviceWorker' in navigator) || !(await isSupported())) return;

        const configUrl = document.querySelector('meta[name="push-config-url"]')?.content;
        if (!configUrl) return;
        this.config = await fetch(configUrl, { headers: { Accept: 'application/json' } }).then((response) => response.json());
        if (!this.config.enabled || !this.config.vapidKey || !this.config.firebase?.projectId) return;

        this.messaging = getMessaging(initializeApp(this.config.firebase, 'myokucare-push'));
        this.renderPanel();
        onMessage(this.messaging, () => this.announce(this.t('push_enabled')));

        if (Notification.permission === 'granted') {
            await this.synchronise(false);
        } else if (Notification.permission === 'denied') {
            this.setState('denied');
        }
    }

    renderPanel() {
        this.panel = document.createElement('aside');
        this.panel.className = 'pwa-push-panel';
        this.panel.setAttribute('aria-live', 'polite');
        this.panel.innerHTML = `
            <div class="pwa-push-icon" aria-hidden="true">●</div>
            <div><strong>${this.t('push_title')}</strong><span data-push-copy>${this.t('push_copy')}</span></div>
            <button class="btn btn-primary" type="button" data-push-toggle>${this.t('push_enable')}</button>
            <button class="pwa-install-close" type="button" data-push-close aria-label="${this.t('push_not_now')}">×</button>`;
        document.body.appendChild(this.panel);
        this.panel.querySelector('[data-push-toggle]').addEventListener('click', () => this.toggle());
        this.panel.querySelector('[data-push-close]').addEventListener('click', () => { this.panel.hidden = true; });
    }

    async toggle() {
        const button = this.panel.querySelector('[data-push-toggle]');
        button.disabled = true;
        try {
            if (this.token) await this.disable();
            else await this.enable();
        } catch {
            this.announce(this.t('push_failed'));
        } finally {
            button.disabled = false;
        }
    }

    async enable() {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            this.setState('denied');
            return;
        }
        await this.synchronise(true);
    }

    async synchronise(announce) {
        const registration = await navigator.serviceWorker.register(this.config.serviceWorkerUrl, {
            scope: this.config.serviceWorkerScope,
        });
        this.token = await getToken(this.messaging, {
            vapidKey: this.config.vapidKey,
            serviceWorkerRegistration: registration,
        });
        if (!this.token) throw new Error('FCM_TOKEN_UNAVAILABLE');

        await this.request(document.querySelector('meta[name="push-subscribe-url"]')?.content, 'POST', {
            token: this.token,
            platform: 'pwa',
            device_name: navigator.userAgent.slice(0, 255),
        });
        this.setState('enabled');
        if (announce) this.announce(this.t('push_enabled'));
    }

    async disable() {
        const token = this.token;
        await this.request(document.querySelector('meta[name="push-unsubscribe-url"]')?.content, 'DELETE', { token });
        await deleteToken(this.messaging);
        this.token = null;
        this.setState('disabled');
        this.announce(this.t('push_disabled'));
    }

    async request(url, method, body) {
        if (!url) throw new Error('PUSH_ENDPOINT_MISSING');
        const response = await fetch(url, {
            method,
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-MyOKUcare-PWA': '1',
            },
            body: JSON.stringify(body),
        });
        if (!response.ok) throw new Error('PUSH_REQUEST_FAILED');
    }

    setState(state) {
        const button = this.panel.querySelector('[data-push-toggle]');
        const copy = this.panel.querySelector('[data-push-copy]');
        if (state === 'enabled') {
            button.textContent = this.t('push_disable');
            copy.textContent = this.t('push_enabled');
        } else if (state === 'denied') {
            button.hidden = true;
            copy.textContent = this.t('push_denied');
        } else {
            button.hidden = false;
            button.textContent = this.t('push_enable');
            copy.textContent = state === 'disabled' ? this.t('push_disabled') : this.t('push_copy');
        }
    }

    announce(message) {
        const copy = this.panel?.querySelector('[data-push-copy]');
        if (copy) copy.textContent = message;
    }
}
