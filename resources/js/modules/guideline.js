export default class Guideline {
    constructor(root) {
        this.root = root;
        this.isOnboarding = root.dataset.onboarding === '1';
        this.version = root.dataset.version || '1';
        this.storageKey = `myokucare-guideline-v${this.version}`;
        this.currentSlide = Number(sessionStorage.getItem('myokucare-guideline-slide') || 1);
        this.totalSlides = root.querySelectorAll('[data-slide]').length;
        this.touchStartX = null;
    }

    init() {
        const alreadyCompleted = this.root.dataset.alreadyCompleted === '1'
            || localStorage.getItem(this.storageKey) === '1';

        if (this.isOnboarding && alreadyCompleted && this.root.dataset.replay !== '1') {
            window.location.replace(this.root.dataset.nextUrl);
            return;
        }

        this.track(this.root.dataset.replay === '1' ? 'REPLAYED' : 'OPENED');
        this.bindLanguageForms();
        this.root.querySelectorAll('[data-guideline-complete]').forEach((button) => {
            button.addEventListener('click', () => this.finish('COMPLETED'));
        });
        this.root.querySelector('[data-guideline-skip]')?.addEventListener('click', () => this.finish('SKIPPED'));

        if (!this.isOnboarding) return;

        this.root.querySelector('[data-slide-next]')?.addEventListener('click', () => this.showSlide(this.currentSlide + 1));
        this.root.querySelector('[data-slide-previous]')?.addEventListener('click', () => this.showSlide(this.currentSlide - 1));
        const slides = this.root.querySelector('.onboarding-slides');
        slides?.addEventListener('touchstart', (event) => { this.touchStartX = event.touches[0]?.clientX ?? null; }, { passive: true });
        slides?.addEventListener('touchend', (event) => {
            if (this.touchStartX === null) return;
            const difference = (event.changedTouches[0]?.clientX ?? this.touchStartX) - this.touchStartX;
            if (Math.abs(difference) > 50) this.showSlide(this.currentSlide + (difference < 0 ? 1 : -1));
            this.touchStartX = null;
        }, { passive: true });
        this.showSlide(this.currentSlide);
    }

    showSlide(slide) {
        this.currentSlide = Math.max(1, Math.min(this.totalSlides, slide));
        sessionStorage.setItem('myokucare-guideline-slide', String(this.currentSlide));
        this.root.querySelectorAll('[data-slide]').forEach((element) => {
            element.hidden = Number(element.dataset.slide) !== this.currentSlide;
        });
        this.root.querySelectorAll('[data-slide-dot]').forEach((dot) => {
            dot.classList.toggle('active', Number(dot.dataset.slideDot) <= this.currentSlide);
        });
        const progress = this.root.querySelector('[data-slide-progress]');
        if (progress) progress.textContent = progress.textContent.replace(/\d+/, String(this.currentSlide));
        const previous = this.root.querySelector('[data-slide-previous]');
        const next = this.root.querySelector('[data-slide-next]');
        const finish = this.root.querySelector('[data-guideline-complete]');
        if (previous) previous.hidden = this.currentSlide === 1;
        if (next) next.hidden = this.currentSlide === this.totalSlides;
        if (finish) finish.hidden = this.currentSlide !== this.totalSlides;
        this.root.querySelector(`[data-slide="${this.currentSlide}"] h1`)?.focus({ preventScroll: true });
    }

    bindLanguageForms() {
        this.root.querySelectorAll('[data-guideline-language]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const formData = new FormData(form);
                formData.set('device_type', this.isStandalone() ? 'PWA' : 'WEB');
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
                    body: formData,
                });
                if (response.ok) window.location.reload();
            });
        });
    }

    async finish(action) {
        localStorage.setItem(this.storageKey, '1');
        sessionStorage.removeItem('myokucare-guideline-slide');
        await this.track(action);
        window.location.assign(this.root.dataset.nextUrl);
    }

    async track(action) {
        try {
            const response = await fetch(this.root.dataset.trackUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken(),
                },
                body: JSON.stringify({ action, device_type: this.isStandalone() ? 'PWA' : 'WEB' }),
            });
            return response.ok;
        } catch {
            return false;
        }
    }

    csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    }
}
