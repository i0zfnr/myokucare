export default class DashboardLive {
    constructor(root, interval = 10000) {
        this.root = root;
        this.endpoint = root.dataset.statisticsUrl;
        this.interval = interval;
        this.loading = false;
        this.numberFormatter = new Intl.NumberFormat('ms-MY');
        this.categoryChart = document.querySelector('[data-category-chart]');
        this.liveLabel = document.querySelector('[data-live-label]');
        this.updatedAt = document.querySelector('[data-live-updated]');
        this.announcement = document.querySelector('[data-dashboard-announcement]');
        this.refreshButton = document.querySelector('[data-dashboard-refresh]');
        this.refreshLabel = document.querySelector('[data-refresh-label]');
        this.refresh = this.refresh.bind(this);
        this.handleVisibility = this.handleVisibility.bind(this);
    }

    init() {
        if (!this.endpoint) return;
        this.refreshButton?.addEventListener('click', this.refresh);
        document.addEventListener('visibilitychange', this.handleVisibility);
        window.addEventListener('online', this.refresh);
        this.refresh();
        this.timer = window.setInterval(() => {
            if (!document.hidden) this.refresh();
        }, this.interval);
    }

    async refresh() {
        if (this.loading) return;
        this.setLoading(true);

        try {
            const response = await fetch(this.endpoint, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const data = await response.json();
            this.updateMetrics(data);
            this.renderCategories(data.categories, data.total);
            if (this.updatedAt) this.updatedAt.textContent = data.updated_at;
            if (this.liveLabel) this.liveLabel.textContent = 'Data langsung';
            this.announce(`Statistik dashboard dikemas kini pada ${data.updated_at}.`);
        } catch {
            if (this.liveLabel) this.liveLabel.textContent = 'Sambungan terganggu';
            this.announce('Statistik tidak dapat dikemas kini. Sistem akan mencuba semula secara automatik.');
        } finally {
            this.setLoading(false);
        }
    }

    updateMetrics(data) {
        document.querySelectorAll('[data-stat]').forEach((element) => {
            const value = data[element.dataset.stat] ?? 0;
            element.textContent = this.numberFormatter.format(value) + (element.dataset.suffix ?? '');
            const card = element.closest('.metric-card');
            const label = card?.querySelector('.metric-content > span')?.textContent;
            if (card && label) card.setAttribute('aria-label', `${label}: ${this.numberFormatter.format(value)}`);
        });
    }

    renderCategories(categories, total) {
        if (!this.categoryChart) return;
        const entries = Object.entries(categories ?? {});

        if (!entries.length) {
            const empty = document.createElement('div');
            empty.className = 'chart-empty';
            empty.innerHTML = '<span aria-hidden="true">◎</span><strong>Belum ada data</strong><p>Statistik akan muncul selepas rekod OKU ditambah.</p>';
            this.categoryChart.replaceChildren(empty);
            return;
        }

        this.categoryChart.replaceChildren(...entries.map(([category, value]) => {
            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
            const row = document.createElement('div');
            row.className = 'category-row';
            row.innerHTML = `<div class="category-label"><strong><span class="category-marker" aria-hidden="true"></span><span data-category-name></span></strong><small><b>${this.numberFormatter.format(value)}</b> orang <em>${percentage}%</em></small></div><div class="progress-track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="${percentage}" aria-label="${category}: ${percentage}%"><span style="width:${percentage}%"></span></div>`;
            row.querySelector('[data-category-name]').textContent = category;
            return row;
        }));
    }

    setLoading(loading) {
        this.loading = loading;
        this.root.setAttribute('aria-busy', String(loading));
        this.root.classList.toggle('is-refreshing', loading);
        if (this.refreshButton) this.refreshButton.disabled = loading;
        if (this.refreshLabel) this.refreshLabel.textContent = loading ? 'Memuat...' : 'Muat Semula';
    }

    announce(message) {
        if (this.announcement) this.announcement.textContent = message;
    }

    handleVisibility() {
        if (!document.hidden) this.refresh();
    }
}
