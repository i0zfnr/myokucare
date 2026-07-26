import { createWorker } from 'tesseract.js';
import jsQR from 'jsqr';

const messages = {
    IMAGE_BLURRY: 'Imej kabur. Pegang kad dengan stabil dan ambil semula.',
    IMAGE_TOO_DARK: 'Imej terlalu gelap. Tambah pencahayaan.',
    IMAGE_OVEREXPOSED: 'Imej terlalu terang. Kurangkan cahaya terus.',
    IMAGE_LOW_RESOLUTION: 'Resolusi imej terlalu rendah.',
    CARD_NOT_DETECTED: 'Kad tidak dapat dikesan. Pastikan semua penjuru berada dalam bingkai.',
    CARD_TOO_SMALL: 'Kad terlalu jauh. Gerakkan kad lebih dekat.',
    CARD_CORNER_MISSING: 'Pastikan semua empat penjuru kad kelihatan.',
    CARD_TILTED: 'Luruskan kad supaya tidak senget.',
    CARD_OBSTRUCTED: 'Alihkan jari atau objek yang menutup kad.',
    GLARE_DETECTED: 'Pantulan cahaya dikesan. Ubah sudut kad.',
    PDF_NOT_ALLOWED: 'Fail PDF tidak dibenarkan.',
    UNSUPPORTED_FILE_TYPE: 'Gunakan imej JPEG, PNG atau WebP sahaja.',
    FILE_SIGNATURE_MISMATCH: 'Jenis fail tidak sepadan dengan kandungannya.',
    FILE_TOO_LARGE: 'Saiz fail terlalu besar.',
    IMAGE_DECODE_FAILED: 'Imej rosak atau tidak dapat dibaca.',
};

export default class IdentityVerification {
    constructor(root) {
        this.root = root;
        this.sessionId = null;
        this.stream = null;
        this.activeStep = null;
        this.documents = new Map();
        this.ocr = {};
        this.worker = null;
        this.csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    }

    init() {
        const consent = this.root.querySelector('[data-consent]');
        const start = this.root.querySelector('[data-start]');
        consent.addEventListener('change', () => { start.disabled = !consent.checked; });
        start.addEventListener('click', () => this.createSession());
        this.root.querySelectorAll('[data-capture-step]').forEach((step) => this.bindStep(step));
        this.root.querySelector('[data-run-verification]').addEventListener('click', () => this.runVerification());
        this.root.querySelector('[data-manual-review]').addEventListener('click', () => this.requestManualReview());
        window.addEventListener('beforeunload', () => this.stopCamera());
    }

    async createSession() {
        const response = await this.fetch(this.root.dataset.createSession, { method: 'POST', body: JSON.stringify({ consent: true }) });
        this.sessionId = response.sessionId;
        this.root.querySelector('[data-consent-panel]').hidden = true;
        this.root.querySelector('[data-verification-workflow]').hidden = false;
    }

    bindStep(step) {
        step.querySelector('[data-open-camera]').addEventListener('click', () => this.openCamera(step));
        step.querySelector('[data-manual-capture]').addEventListener('click', () => this.capture(step));
        step.querySelector('[data-upload-input]').addEventListener('change', (event) => {
            const file = event.target.files?.[0];
            if (file) this.acceptFile(step, file);
        });
        step.querySelector('[data-retake]').addEventListener('click', () => this.resetStep(step, true));
        step.querySelector('[data-remove]').addEventListener('click', () => this.resetStep(step));
    }

    async openCamera(step) {
        this.stopCamera();
        this.activeStep = step;
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } }, audio: false });
            const video = step.querySelector('[data-camera]');
            video.srcObject = this.stream;
            await video.play();
            step.querySelector('[data-manual-capture]').hidden = false;
            step.querySelector('[data-guide-message]').textContent = 'Letakkan kad di dalam bingkai';
            this.monitorCamera(step);
        } catch {
            this.feedback(step, 'Kamera tidak dapat dibuka. Sila benarkan akses kamera atau muat naik imej.');
        }
    }

    monitorCamera(step) {
        let stable = 0;
        let previous = null;
        const tick = () => {
            if (this.activeStep !== step || !this.stream) return;
            const metrics = this.frameMetrics(step);
            if (!metrics) return requestAnimationFrame(tick);
            let message = 'Pegang stabil';
            let valid = metrics.brightness > 55 && metrics.brightness < 220 && metrics.sharpness > 8;
            if (metrics.brightness <= 55) message = 'Tambah pencahayaan';
            else if (metrics.brightness >= 220) message = 'Kurangkan cahaya dan pantulan';
            else if (metrics.sharpness <= 8) message = 'Imej kabur — pegang stabil';
            else if (previous !== null && Math.abs(metrics.signature - previous) < 1.8) stable++;
            else stable = 0;
            previous = metrics.signature;
            step.querySelector('[data-card-guide]').classList.toggle('valid', valid && stable > 8);
            step.querySelector('[data-guide-message]').textContent = valid && stable > 8 ? 'Kad dikesan — pegang stabil' : message;
            if (valid && stable >= 28) return this.capture(step);
            requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    }

    frameMetrics(step) {
        const video = step.querySelector('[data-camera]');
        if (!video.videoWidth) return null;
        const canvas = step.querySelector('[data-camera-canvas]');
        canvas.width = 160; canvas.height = 100;
        const context = canvas.getContext('2d', { willReadFrequently: true });
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const pixels = context.getImageData(0, 0, canvas.width, canvas.height).data;
        let brightness = 0, edges = 0;
        for (let i = 0; i < pixels.length; i += 16) {
            const value = (pixels[i] + pixels[i + 1] + pixels[i + 2]) / 3;
            brightness += value;
            if (i >= 16) edges += Math.abs(value - (pixels[i - 16] + pixels[i - 15] + pixels[i - 14]) / 3);
        }
        const count = pixels.length / 16;
        return { brightness: brightness / count, sharpness: edges / count, signature: brightness / count };
    }

    capture(step) {
        const video = step.querySelector('[data-camera]');
        const canvas = step.querySelector('[data-camera-canvas]');
        canvas.width = video.videoWidth; canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        canvas.toBlob((blob) => this.acceptFile(step, new File([blob], 'camera.jpg', { type: 'image/jpeg' })), 'image/jpeg', 0.94);
        this.stopCamera();
    }

    async acceptFile(step, file) {
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) return this.feedback(step, file.type === 'application/pdf' ? messages.PDF_NOT_ALLOWED : messages.UNSUPPORTED_FILE_TYPE, true);
        const preview = step.querySelector('[data-preview]');
        preview.src = URL.createObjectURL(file); preview.hidden = false;
        this.feedback(step, 'Imej sedang diperiksa…');
        const form = new FormData();
        form.append('document_type', step.dataset.captureStep);
        form.append('image', file);
        try {
            const result = await this.fetch(this.root.dataset.uploadTemplate.replace('__SESSION__', this.sessionId), { method: 'POST', body: form });
            this.documents.set(step.dataset.captureStep, result);
            this.markAccepted(step, result);
            await this.extract(result.processedImageUrl, step.dataset.captureStep);
        } catch (error) {
            const issues = error.data?.issues || Object.values(error.data?.errors || {}).flat();
            this.feedback(step, issues.map((code) => messages[code] || 'Imej tidak memenuhi syarat. Sila ambil semula.').join(' '), true);
            step.querySelector('[data-retake]').hidden = false;
            step.querySelector('[data-remove]').hidden = false;
        }
        this.syncSubmit();
    }

    async extract(url, type) {
        this.worker ||= await createWorker('eng');
        const result = await this.worker.recognize(url);
        this.ocr[type] = { text: result.data.text, confidence: Math.max(0, Math.min(1, result.data.confidence / 100)), edited: false };
        if (type === 'mykad_front') {
            this.root.querySelector('[data-review-text]').value = result.data.text;
            const lines = result.data.text.toUpperCase().split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
            const nric = result.data.text.match(/\b\d{6}[-\s]?\d{2}[-\s]?\d{4}\b/)?.[0] || '';
            const name = lines.find((line) => /^[A-Z @/'.-]{5,}$/.test(line) && !/MALAYSIA|KAD PENGENALAN|IDENTITY CARD/.test(line)) || '';
            const nameInput = this.root.querySelector('[data-review-name]');
            const nricInput = this.root.querySelector('[data-review-nric]');
            nameInput.value = name; nameInput.dataset.initial = name;
            nricInput.value = nric; nricInput.dataset.initial = nric;
        }
        if (type.startsWith('oku_')) await this.scanQr(url);
    }

    async scanQr(url) {
        const image = new Image();
        image.crossOrigin = 'same-origin';
        await new Promise((resolve, reject) => { image.onload = resolve; image.onerror = reject; image.src = url; });
        const canvas = document.createElement('canvas');
        canvas.width = image.naturalWidth; canvas.height = image.naturalHeight;
        const context = canvas.getContext('2d', { willReadFrequently: true });
        context.drawImage(image, 0, 0);
        const code = jsQR(context.getImageData(0, 0, canvas.width, canvas.height).data, canvas.width, canvas.height);
        if (code) this.qr = { payload: code.data, confidence: 1 };
    }

    markAccepted(step) {
        step.classList.add('accepted');
        step.querySelector('[data-capture-state]').textContent = 'Imej diterima';
        step.querySelector('[data-retake]').hidden = false;
        step.querySelector('[data-remove]').hidden = false;
        step.querySelector('[data-open-camera]').hidden = true;
        step.querySelector('[data-upload-input]').closest('label').hidden = true;
        this.feedback(step, 'Imej jelas dan diterima.');
        const order = ['oku_front', 'oku_back', 'mykad_front', 'mykad_back'];
        const index = order.indexOf(step.dataset.captureStep);
        this.root.querySelectorAll('[data-progress-step]').forEach((item, itemIndex) => {
            item.classList.toggle('done', itemIndex <= index);
            item.classList.toggle('active', itemIndex === Math.min(index + 1, 4));
        });
    }

    resetStep(step, reopen = false) {
        this.documents.delete(step.dataset.captureStep);
        delete this.ocr[step.dataset.captureStep];
        step.classList.remove('accepted');
        step.querySelector('[data-preview]').hidden = true;
        step.querySelector('[data-open-camera]').hidden = false;
        step.querySelector('[data-upload-input]').closest('label').hidden = false;
        step.querySelector('[data-retake]').hidden = true;
        step.querySelector('[data-remove]').hidden = true;
        step.querySelector('[data-capture-state]').textContent = 'Belum dihantar';
        this.syncSubmit();
        if (reopen) this.openCamera(step);
    }

    syncSubmit() {
        const ready = this.documents.has('mykad_front') && this.documents.has('mykad_back');
        this.root.querySelector('[data-run-verification]').disabled = !ready;
        this.root.querySelector('[data-submit-help]').textContent = ready ? 'Kedua-dua bahagian MyKad diterima. Anda boleh menjalankan pengesahan.' : 'Bahagian depan dan belakang MyKad mesti diterima sebelum pengesahan boleh dijalankan.';
    }

    async runVerification() {
        const reviewText = this.root.querySelector('[data-review-text]').value;
        const nameInput = this.root.querySelector('[data-review-name]');
        const nricInput = this.root.querySelector('[data-review-nric]');
        const reviewedName = nameInput.value.trim();
        const reviewedNric = nricInput.value.trim();
        const reviewedText = `${reviewedName}\n${reviewedNric}\n${reviewText}`;
        if (this.ocr.mykad_front && (reviewText !== this.ocr.mykad_front.text || reviewedName !== nameInput.dataset.initial || reviewedNric !== nricInput.dataset.initial)) {
            this.ocr.mykad_front.edited = true;
        }
        if (this.ocr.mykad_front) this.ocr.mykad_front.text = reviewedText;
        const processUrl = this.root.dataset.processTemplate.replace('__SESSION__', this.sessionId);
        await this.fetch(processUrl, { method: 'POST', body: JSON.stringify({ documents: this.ocr, qr: this.qr || {} }) });
        const result = await this.fetch(this.root.dataset.verifyTemplate.replace('__SESSION__', this.sessionId), { method: 'POST', body: '{}' });
        this.renderResult(result);
    }

    async requestManualReview() {
        if (!this.sessionId) return;
        const url = this.root.dataset.verifyTemplate.replace('__SESSION__', this.sessionId).replace('/verify', '/manual-review');
        await this.fetch(url, { method: 'POST', body: '{}' });
        this.renderResult({ status: 'MANUAL_REVIEW_REQUIRED', issues: ['USER_REQUESTED_REVIEW'], requiresManualReview: true });
    }

    renderResult(result) {
        const panel = this.root.querySelector('[data-result]');
        const success = ['VERIFIED', 'VERIFIED_LOCALLY_ONLY'].includes(result.status);
        panel.hidden = false;
        panel.className = `panel verification-result ${success ? 'result-success' : 'result-review'}`;
        panel.innerHTML = `<h3>${success ? 'Pengesahan berjaya' : 'Semakan lanjut diperlukan'}</h3>
            <p>Status: <strong>${result.status.replaceAll('_', ' ')}</strong></p>
            ${result.mykad?.nricMasked ? `<p>MyKad: ${result.mykad.nricMasked}</p>` : ''}
            ${result.comparison ? `<p>Padanan nama: ${Math.round(result.comparison.nameSimilarity * 100)}% · Padanan NRIC: ${result.comparison.nricMatch ? 'Ya' : 'Tidak'}</p>` : ''}
            <p>${success ? 'Anda kini boleh menggunakan sistem.' : 'Maklumat anda telah dihantar untuk semakan pegawai. Data QR tidak dianggap disahkan oleh JKM tanpa API rasmi.'}</p>
            ${success ? '<a class="btn btn-primary" href="/dashboard">Terus ke Dashboard</a>' : ''}`;
        panel.scrollIntoView({ behavior: 'smooth' });
    }

    feedback(step, text, error = false) {
        const element = step.querySelector('[data-quality-feedback]');
        element.textContent = text; element.classList.toggle('error-text', error);
    }

    stopCamera() {
        this.stream?.getTracks().forEach((track) => track.stop());
        this.stream = null; this.activeStep = null;
    }

    async fetch(url, options = {}) {
        const headers = options.body instanceof FormData ? {} : { 'Content-Type': 'application/json', Accept: 'application/json' };
        headers['X-CSRF-TOKEN'] = this.csrf;
        const response = await window.fetch(url, { ...options, headers: { ...headers, ...(options.headers || {}) } });
        const data = await response.json();
        if (!response.ok) { const error = new Error(data.message || 'Request failed'); error.data = data; throw error; }
        return data;
    }
}
