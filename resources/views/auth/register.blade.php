<!doctype html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#FF6565">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Daftar akaun MyOKUcare — platform digital JKM untuk pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan kebajikan.">
    <meta property="og:title" content="Daftar Akaun — MyOKUcare">
    <meta property="og:description" content="Cipta akaun MyOKUcare — platform digital JKM untuk pengurusan data OKU, padanan pekerjaan inklusif, dan permohonan kebajikan.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/myokucare-logo.png') }}">
    @include('partials.pwa-head')
    <title>Daftar Akaun — MyOKUcare</title>
    @vite(['resources/css/app.css','resources/css/auth.css','resources/css/animate.css','resources/js/app.js'])
</head>
<body>
<a class="skip-link" href="#register-form">Langkau ke borang pendaftaran</a>
<main class="register-page">
    <section class="register-side" aria-labelledby="register-brand-heading">
        <div class="register-side-inner">
            <div class="register-blob-1"></div>
            <div class="register-blob-2"></div>
            <a class="register-brand" href="{{ route('welcome') }}">
                <span class="brand-mark"><img src="{{ asset('images/myokucare-logo.png') }}" alt=""></span>
                <strong>MyOKUcare</strong>
            </a>
            <div class="register-message">
                <h1 id="register-brand-heading">Sertai komuniti MyOKUcare.</h1>
                <p>Cipta akaun menggunakan e-mel anda. Akses sistem akan disesuaikan berdasarkan peranan yang dipilih.</p>
                <div class="register-benefits" aria-label="Kelebihan sistem">
                    <span>Diselia JKM Malaysia</span>
                    <span>Percuma sepenuhnya</span>
                    <span>Data selamat & terpelihara</span>
                </div>
            </div>
        </div>
    </section>

    <section class="register-wrap" aria-labelledby="register-heading">
        <a class="btn-ghost welcome-button" href="{{ route('welcome') }}" aria-label="Kembali ke laman utama">← Kembali ke Laman Utama</a>
        <form id="register-form" class="register-form" method="post" action="{{ route('register.store') }}" aria-label="Borang pendaftaran" novalidate>
            @csrf

            <p class="eyebrow">Akaun Baharu</p>
            <h2 id="register-heading">Daftar MyOKUcare</h2>
            <p>Lengkapkan maklumat berikut untuk mencipta akaun anda.</p>

            @if($errors->any())
                <div class="register-error" role="alert" aria-live="assertive">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            {{-- Step Indicator --}}
            <div class="register-steps" role="tablist" aria-label="Langkah pendaftaran" id="registerSteps">
                <div class="step-indicator active" role="tab" aria-selected="true" aria-controls="step-1-panel" id="step-1-tab">
                    <span class="step-num">1</span>
                    <span class="step-label">Maklumat Akaun</span>
                </div>
                <div class="step-connector" aria-hidden="true">
                    <div class="step-connector-fill" id="stepConnectorFill"></div>
                </div>
                <div class="step-indicator" role="tab" aria-selected="false" aria-controls="step-2-panel" id="step-2-tab">
                    <span class="step-num">2</span>
                    <span class="step-label">Maklumat OKU</span>
                </div>
            </div>

            <div aria-live="polite" aria-atomic="true" class="sr-only" id="stepAnnouncer"></div>

            {{-- Step 1: Maklumat Akaun --}}
            <div class="step-panel" id="step-1-panel" role="tabpanel" aria-labelledby="step-1-tab" data-step="1">
                <div class="form-group">
                    <label for="name">Nama penuh <span class="required-note">Wajib</span></label>
                    <input class="field" id="name" name="name" type="text" value="{{ old('name') }}" required aria-required="true" autocomplete="name" placeholder="Nama seperti dalam kad pengenalan" aria-describedby="name-error" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                    <p class="field-error" id="name-error" role="alert"></p>
                </div>
                <div class="form-group">
                    <label for="email">Alamat e-mel <span class="required-note">Wajib</span></label>
                    <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required aria-required="true" autocomplete="email" placeholder="nama@contoh.my" aria-describedby="email-error" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    <p class="field-error" id="email-error" role="alert"></p>
                </div>
                <div class="form-group">
                    <label for="role">Daftar sebagai <span class="required-note">Wajib</span></label>
                    <select class="select" id="role" name="role" required aria-required="true" aria-describedby="role-error">
                        <option value="">Pilih peranan</option>
                        <option value="oku_user" @selected(old('role')==='oku_user')>Pengguna OKU</option>
                        <option value="employer" @selected(old('role')==='employer')>Majikan</option>
                    </select>
                    <p class="field-error" id="role-error" role="alert"></p>
                </div>
                <div class="form-group">
                    <label for="password">Kata laluan <span class="required-note">Wajib</span></label>
                    <div class="password-field">
                        <input class="field" id="password" name="password" type="password" required aria-required="true" autocomplete="new-password" placeholder="Minimum 8 aksara" aria-describedby="password-error password-strength" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                        <button class="password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Tunjukkan kata laluan" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                        </button>
                    </div>
                    <div class="password-strength" id="password-strength" role="status" aria-live="polite">
                        <div class="password-strength-bar">
                            <div class="password-strength-fill" id="pwStrengthFill"></div>
                        </div>
                        <span class="password-strength-label" id="pwStrengthLabel"></span>
                    </div>
                    <p class="field-error" id="password-error" role="alert"></p>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Sahkan kata laluan <span class="required-note">Wajib</span></label>
                    <div class="password-field">
                        <input class="field" id="password_confirmation" name="password_confirmation" type="password" required aria-required="true" autocomplete="new-password" placeholder="Taip semula kata laluan" aria-describedby="password-confirmation-error" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}">
                        <button class="password-toggle" type="button" data-password-toggle aria-controls="password_confirmation" aria-label="Tunjukkan kata laluan" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.8"/></svg>
                        </button>
                    </div>
                    <p class="field-error" id="password-confirmation-error" role="alert"></p>
                </div>
                <div class="step-actions">
                    <button class="btn btn-primary step-next" type="button" data-step-next>Seterusnya →</button>
                </div>
            </div>

            {{-- Step 2: Maklumat OKU --}}
            <div class="step-panel" id="step-2-panel" role="tabpanel" aria-labelledby="step-2-tab" data-step="2" hidden>
                <section class="oku-signup-fields" id="okuSignupFields" aria-label="Maklumat pendaftaran OKU">
                    <div class="signup-section-title">
                        <strong>Maklumat Pendaftaran OKU</strong>
                        <span>Sistem akan menyemak kelengkapan dan rekod pendua sebelum akaun diwujudkan.</span>
                    </div>
                    <p class="signup-card-note">
                        <span aria-hidden="true">ⓘ</span>
                        <span>Gambar Kad OKU tidak diperlukan di halaman ini. Selepas pendaftaran, log masuk dan muat naik gambar kad melalui Profil Kerjaya.</span>
                    </p>
                    <div class="signup-fields-list">
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="ic_number">No. kad pengenalan <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="ic_number" name="ic_number" value="{{ old('ic_number') }}" inputmode="numeric" autocomplete="off" required aria-required="true" placeholder="000101-10-1234" aria-describedby="ic-number-error" aria-invalid="{{ $errors->has('ic_number') ? 'true' : 'false' }}" data-format-ic>
                                <p class="field-error" id="ic-number-error" role="alert"></p>
                            </div>
                            <div class="form-group">
                                <label for="phone_number">No. telefon <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" required aria-required="true" placeholder="012-345 6789" aria-describedby="phone-error" aria-invalid="{{ $errors->has('phone_number') ? 'true' : 'false' }}">
                                <p class="field-error" id="phone-error" role="alert"></p>
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="gender">Jantina <span class="required-note">Wajib</span></label>
                                <select class="select oku-required" id="gender" name="gender" required aria-required="true" aria-describedby="gender-error">
                                    <option value="">Pilih jantina</option>
                                    @foreach(['Lelaki','Perempuan'] as $value)
                                        <option @selected(old('gender')===$value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <p class="field-error" id="gender-error" role="alert"></p>
                            </div>
                            <div class="form-group">
                                <label for="age">Umur <span class="required-note">Wajib</span> <small class="field-hint">min. 16</small></label>
                                <input class="field oku-required" id="age" name="age" type="number" min="16" max="120" value="{{ old('age') }}" required aria-required="true" aria-describedby="age-error" aria-invalid="{{ $errors->has('age') ? 'true' : 'false' }}">
                                <p class="field-error" id="age-error" role="alert"></p>
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="marital_status">Status perkahwinan <span class="required-note">Wajib</span></label>
                                <select class="select oku-required" id="marital_status" name="marital_status" required aria-required="true" aria-describedby="marital-status-error">
                                    <option value="">Pilih status</option>
                                    @foreach(['Berkahwin','Bujang','Duda','Janda'] as $value)
                                        <option @selected(old('marital_status')===$value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <p class="field-error" id="marital-status-error" role="alert"></p>
                            </div>
                            <div class="form-group">
                                <label for="education_level">Pendidikan tertinggi <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="education_level" name="education_level" value="{{ old('education_level') }}" required aria-required="true" placeholder="Cth: SPM, Diploma, Ijazah" aria-describedby="education-level-error" aria-invalid="{{ $errors->has('education_level') ? 'true' : 'false' }}">
                                <p class="field-error" id="education-level-error" role="alert"></p>
                            </div>
                        </div>
                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="oku_card_number">No. pendaftaran OKU <span class="required-note">Wajib</span></label>
                                <input class="field oku-required" id="oku_card_number" name="oku_card_number" value="{{ old('oku_card_number') }}" required aria-required="true" aria-describedby="oku-card-error" aria-invalid="{{ $errors->has('oku_card_number') ? 'true' : 'false' }}">
                                <p class="field-error" id="oku-card-error" role="alert"></p>
                            </div>
                            <div class="form-group">
                                <label for="oku_category">Kategori OKU <span class="required-note">Wajib</span></label>
                                <select class="select oku-required" id="oku_category" name="oku_category" required aria-required="true" aria-describedby="oku-category-error">
                                    <option value="">Pilih kategori</option>
                                    @foreach(['Fizikal','Penglihatan','Pendengaran','Pertuturan','Pembelajaran','Mental','Pelbagai'] as $value)
                                        <option @selected(old('oku_category')===$value)>{{ $value }}</option>
                                    @endforeach
                                </select>
                                <p class="field-error" id="oku-category-error" role="alert"></p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sektor_pekerjaan">Sektor pekerjaan <span class="required-note">Wajib</span></label>
                            <select class="select oku-required" id="sektor_pekerjaan" name="sektor_pekerjaan" required aria-required="true" aria-describedby="sektor-error">
                                <option value="">Pilih sektor</option>
                                @foreach(['Sektor Awam','Sektor Swasta','Bekerja Sendiri','Tidak Bekerja'] as $value)
                                    <option @selected(old('sektor_pekerjaan')===$value)>{{ $value }}</option>
                                @endforeach
                            </select>
                            <p class="field-error" id="sektor-error" role="alert"></p>
                        </div>
                        <div class="form-group">
                            <label>Jenis bantuan (pilih yang berkenaan)</label>
                            <div class="oku-check-grid" role="group" aria-label="Jenis bantuan">
                                @foreach(['EPOKU'=>'Elaun Pekerja OKU','BTB'=>'Bantuan OKU Tidak Berupaya Bekerja','BPT'=>'Bantuan Penjagaan OKU Terlantar','BAT'=>'Bantuan Alat Sokongan/Tiruan','Lain-lain'=>'Lain-lain','Tiada'=>'Tiada'] as $val=>$label)
                                    <label class="check-option">
                                        <input name="jenis_bantuan[]" type="checkbox" value="{{ $val }}" @if(is_array(old('jenis_bantuan'))&&in_array($val,old('jenis_bantuan'),true)) checked @endif>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Alamat penuh <span class="required-note">Wajib</span></label>
                            <textarea class="textarea oku-required" id="address" name="address" rows="3" required aria-required="true" aria-describedby="address-error" aria-invalid="{{ $errors->has('address') ? 'true' : 'false' }}">{{ old('address') }}</textarea>
                            <p class="field-error" id="address-error" role="alert"></p>
                        </div>
                    </div>
                </section>

                <div class="step-actions step-actions-2">
                    <button class="btn btn-secondary step-back" type="button" data-step-back>← Kembali</button>
                    <button class="btn btn-primary register-submit btn-ripple" type="submit" id="registerSubmitBtn">
                        <span class="submit-label">Cipta Akaun</span>
                        <span class="submit-spinner" hidden aria-hidden="true"></span>
                    </button>
                </div>
            </div>

            <p class="signin-note">Sudah mempunyai akaun? <a href="{{ route('login') }}">Log masuk di sini</a></p>
        </form>
    </section>
</main>

{{-- Mobile sticky bottom bar --}}
<div class="register-sticky-bar" id="registerStickyBar" hidden>
    <div class="sticky-bar-inner">
        <span class="sticky-step-text" id="stickyStepText">Langkah 1 daripada 2</span>
        <button class="btn btn-primary btn-primary-sm sticky-next" type="button" id="stickyNextBtn">Seterusnya →</button>
        <button class="btn btn-primary btn-primary-sm register-submit sticky-submit" type="submit" form="register-form" id="stickySubmitBtn" hidden>
            <span class="submit-label">Cipta Akaun</span>
            <span class="submit-spinner" hidden aria-hidden="true"></span>
        </button>
    </div>
</div>

<script>
(function() {
    'use strict';

    var form = document.getElementById('register-form');
    var roleSelect = document.getElementById('role');
    var okuFields = document.getElementById('okuSignupFields');
    var okuRequired = okuFields ? okuFields.querySelectorAll('.oku-required') : [];
    var step1Panel = document.getElementById('step-1-panel');
    var step2Panel = document.getElementById('step-2-panel');
    var step1Tab = document.getElementById('step-1-tab');
    var step2Tab = document.getElementById('step-2-tab');
    var stickyBar = document.getElementById('registerStickyBar');
    var stickyStepText = document.getElementById('stickyStepText');
    var stickyNextBtn = document.getElementById('stickyNextBtn');
    var stickySubmitBtn = document.getElementById('stickySubmitBtn');
    var connectorFill = document.getElementById('stepConnectorFill');
    var stepAnnouncer = document.getElementById('stepAnnouncer');
    var pwInput = document.getElementById('password');
    var pwStrengthFill = document.getElementById('pwStrengthFill');
    var pwStrengthLabel = document.getElementById('pwStrengthLabel');
    var icInput = document.getElementById('ic_number');
    var submitBtn = document.getElementById('registerSubmitBtn');
    var stickySubmitBtnInner = stickySubmitBtn;

    var currentStep = 1;

    // ── Password strength ──
    function calcPasswordStrength(pwd) {
        if (!pwd) return { pct:0, label:'', cls:'' };
        var score = 0;
        if (pwd.length >= 8) score += 25;
        if (pwd.length >= 12) score += 25;
        if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 25;
        if (/\d/.test(pwd)) score += 12.5;
        if (/[^a-zA-Z0-9]/.test(pwd)) score += 12.5;
        if (score >= 80) return { pct:100, label:'Kuat', cls:'strong' };
        if (score >= 50) return { pct:score, label:'Sederhana', cls:'medium' };
        if (score >= 25) return { pct:score, label:'Lemah', cls:'weak' };
        return { pct:score, label:'Lemah', cls:'weak' };
    }

    function updatePasswordStrength() {
        if (!pwInput || !pwStrengthFill || !pwStrengthLabel) return;
        var val = pwInput.value;
        var result = calcPasswordStrength(val);
        pwStrengthFill.style.width = result.pct + '%';
        pwStrengthFill.className = 'password-strength-fill ' + result.cls;
        pwStrengthLabel.textContent = result.label;
        pwStrengthLabel.className = 'password-strength-label ' + result.cls;
    }

    if (pwInput) {
        pwInput.addEventListener('input', updatePasswordStrength);
    }

    // ── IC number formatting ──
    function formatIC(e) {
        var input = e.target;
        var val = input.value.replace(/\D/g, '');
        var formatted = '';
        if (val.length > 6) { formatted = val.substring(0,6) + '-' + val.substring(6); }
        else { formatted = val; }
        if (formatted.length > 9) { formatted = formatted.substring(0,9) + '-' + formatted.substring(9); }
        input.value = formatted.substring(0, 14);
    }

    if (icInput) {
        icInput.addEventListener('input', formatIC);
    }

    // ── Per-field validation ──
    var VALIDATION_RULES = {
        name: { test: function(v) { return v.trim().length >= 2; }, msg: 'Nama mestilah sekurang-kurangnya 2 aksara.' },
        email: { test: function(v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v); }, msg: 'Alamat e-mel tidak sah.' },
        password: { test: function(v) { return v.length >= 8; }, msg: 'Kata laluan mestilah sekurang-kurangnya 8 aksara.' },
        password_confirmation: { test: function(v) { return v === (pwInput ? pwInput.value : ''); }, msg: 'Kata laluan tidak sama.' },
        ic_number: { test: function(v) { return /^\d{6}-\d{2}-\d{4}$/.test(v); }, msg: 'Format tidak sah. Gunakan format 000101-10-1234.' },
        phone_number: { test: function(v) { return v.replace(/[\s-]/g,'').length >= 9; }, msg: 'Nombor telefon tidak sah.' },
        age: { test: function(v) { var n=parseInt(v,10); return n>=16 && n<=120; }, msg: 'Umur mestilah antara 16 dan 120.' },
        oku_card_number: { test: function(v) { return v.trim().length >= 3; }, msg: 'Nombor pendaftaran OKU tidak sah.' },
        address: { test: function(v) { return v.trim().length >= 10; }, msg: 'Alamat mestilah sekurang-kurangnya 10 aksara.' }
    };

    function getErrorId(input) {
        return input.getAttribute('aria-describedby') ? input.getAttribute('aria-describedby').split(' ')[0] : null;
    }

    function validateField(input) {
        var errorId = getErrorId(input);
        var errorEl = errorId ? document.getElementById(errorId) : null;
        var name = input.name;
        var rule = VALIDATION_RULES[name];
        var value = input.value || '';
        var isValid = true;

        if (input.required && !value.trim()) {
            isValid = false;
            if (errorEl) errorEl.textContent = 'Medan ini diperlukan.';
        } else if (rule && value.trim()) {
            isValid = rule.test(value);
            if (errorEl) errorEl.textContent = isValid ? '' : rule.msg;
        } else {
            if (errorEl) errorEl.textContent = '';
        }

        input.setAttribute('aria-invalid', isValid ? 'false' : 'true');
        return isValid;
    }

    function validateAllStep1() {
        var fields = step1Panel.querySelectorAll('.field, .select');
        var allValid = true;
        fields.forEach(function(f) {
            if (!validateField(f)) allValid = false;
        });
        // Extra: check password match
        var pwConfirm = document.getElementById('password_confirmation');
        if (pwConfirm && pwConfirm.value && pwInput && pwInput.value !== pwConfirm.value) {
            var errEl = document.getElementById('password-confirmation-error');
            if (errEl) errEl.textContent = 'Kata laluan tidak sama.';
            pwConfirm.setAttribute('aria-invalid', 'true');
            allValid = false;
        }
        return allValid;
    }

    // Attach blur validation
    document.querySelectorAll('#step-1-panel .field, #step-1-panel .select, #step-2-panel .field, #step-2-panel .select, #step-2-panel .textarea').forEach(function(input) {
        input.addEventListener('blur', function() { validateField(this); });
    });

    // ── Step navigation ──
    function showStep(step) {
        currentStep = step;
        step1Panel.hidden = step !== 1;
        step2Panel.hidden = step !== 2;
        step1Tab.classList.toggle('active', step === 1);
        step2Tab.classList.toggle('active', step === 2);
        step1Tab.setAttribute('aria-selected', step === 1 ? 'true' : 'false');
        step2Tab.setAttribute('aria-selected', step === 2 ? 'true' : 'false');

        // Connector animation
        if (connectorFill) {
            connectorFill.style.width = step === 2 ? '100%' : '0%';
        }

        // Screen reader announcement
        if (stepAnnouncer) {
            stepAnnouncer.textContent = 'Langkah ' + step + ' daripada 2';
        }

        if (stickyBar) {
            if (step === 1) {
                stickyStepText.textContent = 'Langkah 1 daripada 2';
                stickyNextBtn.hidden = false;
                stickySubmitBtn.hidden = true;
            } else {
                stickyStepText.textContent = 'Langkah 2 daripada 2';
                stickyNextBtn.hidden = true;
                stickySubmitBtn.hidden = false;
            }
        }
    }

    document.querySelectorAll('[data-step-next]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!validateAllStep1()) {
                // Focus first invalid field
                var firstInvalid = step1Panel.querySelector('[aria-invalid="true"]');
                if (firstInvalid) firstInvalid.focus();
                return;
            }
            if (!roleSelect || roleSelect.value !== 'oku_user') {
                form.submit();
                return;
            }
            showStep(2);
            // Focus first field in step 2
            var firstField = step2Panel.querySelector('.field, .select');
            if (firstField) setTimeout(function() { firstField.focus(); }, 100);
        });
    });

    document.querySelectorAll('[data-step-back]').forEach(function(btn) {
        btn.addEventListener('click', function() { showStep(1); });
    });

    if (stickyNextBtn) {
        stickyNextBtn.addEventListener('click', function() {
            if (!validateAllStep1()) {
                var firstInvalid = step1Panel.querySelector('[aria-invalid="true"]');
                if (firstInvalid) firstInvalid.focus();
                return;
            }
            if (!roleSelect || roleSelect.value !== 'oku_user') {
                form.submit();
                return;
            }
            showStep(2);
        });
    }

    // ── OKU fields sync ──
    function syncOkuFields() {
        var active = roleSelect && roleSelect.value === 'oku_user';
        if (okuFields) okuFields.hidden = !active;
        okuRequired.forEach(function(field) { field.required = active; });
        if (!active && currentStep === 2) showStep(1);
    }

    if (roleSelect) {
        roleSelect.addEventListener('change', syncOkuFields);
        syncOkuFields();
    }

    // ── Loading state on submit ──
    function setLoading(loading) {
        var btns = [submitBtn, stickySubmitBtnInner];
        btns.forEach(function(btn) {
            if (!btn) return;
            var label = btn.querySelector('.submit-label');
            var spinner = btn.querySelector('.submit-spinner');
            if (loading) {
                btn.disabled = true;
                if (label) label.textContent = 'Memproses...';
                if (spinner) spinner.hidden = false;
            } else {
                btn.disabled = false;
                if (label) label.textContent = 'Cipta Akaun';
                if (spinner) spinner.hidden = true;
            }
        });
    }

    form.addEventListener('submit', function(e) {
        // Validate all visible fields
        var visibleFields = form.querySelectorAll('.step-panel:not([hidden]) .field, .step-panel:not([hidden]) .select, .step-panel:not([hidden]) .textarea');
        var allValid = true;
        visibleFields.forEach(function(f) {
            if (!validateField(f)) allValid = false;
        });
        if (!allValid) {
            e.preventDefault();
            var firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) firstInvalid.focus();
            return;
        }
        setLoading(true);
    });

    // ── Sticky bar scroll detection ──
    var formTop = form ? form.getBoundingClientRect().top + window.scrollY : 0;
    function checkSticky() {
        if (window.innerWidth > 850) {
            if (stickyBar) stickyBar.hidden = true;
            return;
        }
        var scrollY = window.scrollY;
        var shouldShow = scrollY > formTop + 100;
        if (stickyBar) stickyBar.hidden = !shouldShow;
    }
    window.addEventListener('scroll', checkSticky, { passive: true });
    window.addEventListener('resize', checkSticky);
    checkSticky();

    // ── Show correct step after validation errors ──
    if (document.querySelector('.register-error')) {
        var step2Fields = step2Panel.querySelectorAll('.field, .select, .textarea');
        var hasStep2Data = false;
        step2Fields.forEach(function(f) {
            if (f.value && f.value.trim() !== '') hasStep2Data = true;
        });
        if (hasStep2Data && roleSelect && roleSelect.value === 'oku_user') showStep(2);
    }
})();
</script>
</body></html>
