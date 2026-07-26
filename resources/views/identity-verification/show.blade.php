@extends('layout', ['title' => 'Pengesahan Identiti'])

@section('content')
<div class="identity-verification"
    data-identity-verification
    data-create-session="{{ route('identity-verification.session.create') }}"
    data-upload-template="{{ route('identity-verification.upload', ['session' => '__SESSION__']) }}"
    data-process-template="{{ route('identity-verification.process', ['session' => '__SESSION__']) }}"
    data-verify-template="{{ route('identity-verification.verify', ['session' => '__SESSION__']) }}">
    <div class="page-head">
        <div>
            <p class="eyebrow">Pengesahan Identiti Selamat</p>
            <h2>Sahkan Kad OKU dan MyKad anda</h2>
            <p>Imej diproses secara peribadi dan tidak dihantar kepada perkhidmatan OCR pihak ketiga.</p>
        </div>
        <span class="verification-pill status-{{ strtolower($status) }}">{{ str_replace('_', ' ', $status) }}</span>
    </div>

    @if(session('warning'))<div class="error" role="alert">{{ session('warning') }}</div>@endif

    <ol class="identity-progress" aria-label="Kemajuan pengesahan">
        @foreach(['Kad OKU Depan', 'Kad OKU Belakang', 'MyKad Depan', 'MyKad Belakang', 'Semakan', 'Keputusan'] as $index => $label)
            <li class="{{ $index === 0 ? 'active' : '' }}" data-progress-step="{{ $index + 1 }}"><span>{{ $index + 1 }}</span><small>{{ $label }}</small></li>
        @endforeach
    </ol>

    <section class="panel identity-consent" data-consent-panel>
        <h3>Persetujuan diperlukan</h3>
        <p>Kad pengenalan mengandungi data peribadi sensitif. Imej akan disimpan secara sulit untuk tujuan pengesahan, semakan yang dibenarkan, dan dipadam mengikut polisi penyimpanan.</p>
        <label class="check-option"><input type="checkbox" data-consent><span>Saya bersetuju dengan pengumpulan dan pemprosesan imej Kad OKU dan MyKad untuk pengesahan identiti.</span></label>
        <button class="btn btn-primary" type="button" data-start disabled>Mulakan Pengesahan</button>
    </section>

    <div data-verification-workflow hidden>
        @foreach([
            'oku_front' => ['Kad OKU — Bahagian Depan', 'Letakkan bahagian depan Kad OKU di dalam bingkai.', false],
            'oku_back' => ['Kad OKU — Bahagian Belakang', 'Pastikan kod QR dan semua penjuru jelas.', false],
            'mykad_front' => ['MyKad — Bahagian Depan', 'Pastikan nama, nombor MyKad dan gambar jelas.', true],
            'mykad_back' => ['MyKad — Bahagian Belakang', 'Bahagian belakang MyKad adalah wajib.', true],
        ] as $type => [$title, $instruction, $mandatory])
            <section class="panel capture-step" data-capture-step="{{ $type }}" data-mandatory="{{ $mandatory ? '1' : '0' }}">
                <div class="capture-step-head">
                    <div><h3>{{ $title }} @if($mandatory)<span>Wajib</span>@endif</h3><p>{{ $instruction }}</p></div>
                    <span class="capture-state" data-capture-state>Belum dihantar</span>
                </div>
                <div class="capture-layout">
                    <div class="camera-stage">
                        <video playsinline muted data-camera></video>
                        <canvas data-camera-canvas hidden></canvas>
                        <div class="card-guide" data-card-guide>
                            <i></i><i></i><i></i><i></i>
                            <span data-guide-message>Letakkan kad di dalam bingkai</span>
                        </div>
                        <img data-preview alt="Pratonton {{ $title }}" hidden>
                    </div>
                    <div class="capture-controls">
                        <div class="quality-feedback" data-quality-feedback role="status">Pilih kamera atau muat naik imej.</div>
                        <button class="btn" type="button" data-open-camera>Gunakan Kamera</button>
                        <button class="btn btn-primary" type="button" data-manual-capture hidden>Ambil Gambar</button>
                        <label class="btn upload-button">Muat Naik Imej
                            <input type="file" accept="image/jpeg,image/png,image/webp" data-upload-input hidden>
                        </label>
                        <button class="btn" type="button" data-retake hidden>Ambil Semula</button>
                        <button class="btn btn-danger-soft" type="button" data-remove hidden>Buang Imej</button>
                        <small>JPEG, PNG atau WebP sahaja · maksimum {{ config('identity_verification.max_upload_kb') / 1024 }} MB · PDF tidak dibenarkan.</small>
                    </div>
                </div>
            </section>
        @endforeach

        <section class="panel extraction-review" data-review-panel>
            <h3>Semak maklumat yang diekstrak</h3>
            <p>Betulkan hanya jika OCR tersilap. Sebarang perubahan pengguna akan dihantar untuk semakan manual.</p>
            <div class="form-grid">
                <div class="form-group"><label for="ocr-name">Nama penuh MyKad</label><input class="field" id="ocr-name" data-review-name autocomplete="off"></div>
                <div class="form-group"><label for="ocr-nric">Nombor MyKad</label><input class="field" id="ocr-nric" data-review-nric inputmode="numeric" autocomplete="off"></div>
                <div class="form-group full"><label for="ocr-text">Teks OCR MyKad</label><textarea class="textarea" id="ocr-text" data-review-text rows="5"></textarea></div>
            </div>
        </section>

        <div class="identity-submit-bar">
            <p data-submit-help>Bahagian depan dan belakang MyKad mesti diterima sebelum pengesahan boleh dijalankan.</p>
            <button class="btn" type="button" data-manual-review>Hantar untuk Semakan Manual</button>
            <button class="btn btn-primary" type="button" data-run-verification disabled>Jalankan Pengesahan</button>
        </div>

        <section class="panel verification-result" data-result hidden aria-live="polite"></section>
    </div>
</div>
@endsection
