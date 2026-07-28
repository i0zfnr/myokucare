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
            <p class="eyebrow">{{ __('ui.pengesahan_identiti_selamat.5d7ba03c') }}</p>
            <h2>{{ __('ui.sahkan_kad_oku_dan_mykad_anda.d4c68caa') }}</h2>
            <p>{{ __('ui.imej_diproses_secara_peribadi_dan_tidak_dihantar.8cca58ed') }}</p>
        </div>
        <span class="verification-pill status-{{ strtolower($status) }}">{{ str_replace('_', ' ', $status) }}</span>
    </div>

    @if(session('warning'))<div class="error" role="alert">{{ session('warning') }}</div>@endif

    <ol class="identity-progress" aria-label="{{ __('ui.kemajuan_pengesahan.6f9459b5') }}">
        @foreach(['Kad OKU Depan', 'Kad OKU Belakang', 'MyKad Depan', 'MyKad Belakang', 'Semakan', 'Keputusan'] as $index => $label)
            <li class="{{ $index === 0 ? 'active' : '' }}" data-progress-step="{{ $index + 1 }}"><span>{{ $index + 1 }}</span><small>{{ $label }}</small></li>
        @endforeach
    </ol>

    <section class="panel identity-consent" data-consent-panel>
        <h3>{{ __('ui.persetujuan_diperlukan.9c9725f6') }}</h3>
        <p>{{ __('ui.kad_pengenalan_mengandungi_data_peribadi_sensitif_imej.847adbda') }}</p>
        <label class="check-option"><input type="checkbox" data-consent><span>{{ __('ui.saya_bersetuju_dengan_pengumpulan_dan_pemprosesan_imej.0a6f952d') }}</span></label>
        <button class="btn btn-primary" type="button" data-start disabled>{{ __('ui.mulakan_pengesahan.e65e4166') }}</button>
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
                    <div><h3>{{ $title }} @if($mandatory)<span>{{ __('ui.wajib.e6bc023e') }}</span>@endif</h3><p>{{ $instruction }}</p></div>
                    <span class="capture-state" data-capture-state>{{ __('ui.belum_dihantar.cd0f8008') }}</span>
                </div>
                <div class="capture-layout">
                    <div class="camera-stage">
                        <video playsinline muted data-camera></video>
                        <canvas data-camera-canvas hidden></canvas>
                        <div class="card-guide" data-card-guide>
                            <i></i><i></i><i></i><i></i>
                            <span data-guide-message>{{ __('ui.letakkan_kad_di_dalam_bingkai.ac4fe6e8') }}</span>
                        </div>
                        <img data-preview alt="Pratonton {{ $title }}" hidden>
                    </div>
                    <div class="capture-controls">
                        <div class="quality-feedback" data-quality-feedback role="status">{{ __('ui.pilih_kamera_atau_muat_naik_imej.da99a45e') }}</div>
                        <button class="btn" type="button" data-open-camera>{{ __('ui.gunakan_kamera.c4ff31d8') }}</button>
                        <button class="btn btn-primary" type="button" data-manual-capture hidden>{{ __('ui.ambil_gambar.8ede3b9e') }}</button>
                        <label class="btn upload-button">{{ __('ui.muat_naik_imej.9fe37fa6') }}
                            <input type="file" accept="image/jpeg,image/png,image/webp" data-upload-input hidden>
                        </label>
                        <button class="btn" type="button" data-retake hidden>{{ __('ui.ambil_semula.825ee67d') }}</button>
                        <button class="btn btn-danger-soft" type="button" data-remove hidden>{{ __('ui.buang_imej.c6bcaf34') }}</button>
                        <small>JPEG, PNG atau WebP sahaja · maksimum {{ config('identity_verification.max_upload_kb') / 1024 }} MB · PDF tidak dibenarkan.</small>
                    </div>
                </div>
            </section>
        @endforeach

        <section class="panel extraction-review" data-review-panel>
            <h3>{{ __('ui.semak_maklumat_yang_diekstrak.6e642951') }}</h3>
            <p>{{ __('ui.betulkan_hanya_jika_ocr_tersilap_sebarang_perubahan.7fd736a1') }}</p>
            <div class="form-grid">
                <div class="form-group"><label for="ocr-name">{{ __('ui.nama_penuh_mykad.edad2a36') }}</label><input class="field" id="ocr-name" data-review-name autocomplete="off"></div>
                <div class="form-group"><label for="ocr-nric">{{ __('ui.nombor_mykad.f3a07902') }}</label><input class="field" id="ocr-nric" data-review-nric inputmode="numeric" autocomplete="off"></div>
                <div class="form-group full"><label for="ocr-text">{{ __('ui.teks_ocr_mykad.20552de4') }}</label><textarea class="textarea" id="ocr-text" data-review-text rows="5"></textarea></div>
            </div>
        </section>

        <div class="identity-submit-bar">
            <p data-submit-help>{{ __('ui.bahagian_depan_dan_belakang_mykad_mesti_diterima.60fdd053') }}</p>
            <button class="btn" type="button" data-manual-review>{{ __('ui.hantar_untuk_semakan_manual.2ef09ac2') }}</button>
            <button class="btn btn-primary" type="button" data-run-verification disabled>{{ __('ui.jalankan_pengesahan.fe87134d') }}</button>
        </div>

        <section class="panel verification-result" data-result hidden aria-live="polite"></section>
    </div>
</div>
@endsection
